<?php

/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon-page.class.php');

if ( !class_exists('CUAR_AbstractEditContentPageAddOn')) :

    /**
     * The base class for addons that should render a page to edit (create or update) private content
     *
     * @author Vincent Prat @ MarvinLabs
     */
    abstract class CUAR_AbstractEditContentPageAddOn extends CUAR_AbstractPageAddOn
    {

        protected $should_print_form = true;
        protected $form_errors = array();
        protected $form_success = array();
        protected $form_messages = array();
        protected $current_post_id = null;
        protected $current_post = null;

        public function __construct($addon_id = null)
        {
            parent::__construct($addon_id);
        }

        protected function set_page_parameters($priority, $description)
        {
            parent::set_page_parameters($priority, $description);

            if ( !isset($this->page_description['friendly_post_type'])) {
                $this->page_description['friendly_post_type'] = null;
            }

            if ( !isset($this->page_description['friendly_taxonomy'])) {
                $this->page_description['friendly_taxonomy'] = null;
            }
        }

        /**
         * The action done by this page (e.g. create or update)
         */
        protected abstract function get_action();

        public function get_type()
        {
            return $this->get_action() . '-content';
        }

        public function run_addon($plugin)
        {
            parent::run_addon($plugin);

            if ( !is_admin()) {
                add_action('template_redirect', array(&$this, 'handle_form_submission'));
                add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
            } else {
                add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));
                add_filter('cuar/core/permission-groups', array(&$this, 'get_configurable_capability_groups'), 1000);
            }

	        // Ajax Summernote Insert/Delete image
	        add_action('wp_ajax_cuar_insert_image', array(&$this, 'ajax_insert_image'));
	        add_action('wp_ajax_cuar_delete_image', array(&$this, 'ajax_delete_image'));

            if ($this->get_wizard_step_count() > 1) {
                // Enable rewrite rules for the wizard steps
                $this->enable_wizard_permalinks();
            }
        }

        /*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

        public function get_friendly_post_type()
        {
            return $this->page_description['friendly_post_type'];
        }

        public function get_friendly_taxonomy()
        {
            return $this->page_description['friendly_taxonomy'];
        }

        public function handle_form_submission()
        {
            if (!$this->is_currently_displayed()) return false;

            $action = $this->get_action();
            if (get_query_var('cuar_action', null) != null) {
                $action = get_query_var('cuar_action', null);
            }

            if ( !$this->is_action_authorized($action)) {
                return false;
            }

            // If not submitting the form, just stop here
            if ( !isset($_POST['cuar_do_register']) && !isset($_GET["nonce"])) {
                return true;
            }

            // Form ID should match
            if (isset($_POST['cuar_form_id']) && $_POST['cuar_form_id'] != $this->get_slug()) {
                return false;
            }

            // Nonce check
            $nonce = isset($_POST["cuar_" . $this->get_slug() . "_nonce"])
                ? $_POST["cuar_" . $this->get_slug() . "_nonce"]
                : (isset($_GET["nonce"]) ? $_GET["nonce"] : '');

            if ( !wp_verify_nonce($nonce, 'cuar_' . $this->get_slug())) {
                die('An attempt to bypass security checks was detected! Please go back and try again.');
            }

            do_action('cuar/private-content/edit/before_' . $action, $this, $this->form_errors, $_POST);
            do_action('cuar/private-content/edit/before_' . $action . '/page-slug=' . $this->get_slug(), $this, $this->form_errors, $_POST);

            $result = $this->do_edit_content($action, $_POST);

            do_action('cuar/private-content/edit/after_' . $action, $this, $this->form_errors, $_POST);
            do_action('cuar/private-content/edit/after_' . $action . '/page-slug=' . $this->get_slug(), $this, $this->form_errors, $_POST);

            if (true === $result && empty($this->form_errors)) {
                // If we still have some wizard steps to go, redirect to the next step
                $step_count = $this->get_wizard_step_count();
                if ($action != 'delete' && $action != 'update' && $step_count > 1) {
                    $current_step = $this->get_current_wizard_step();
                    if ($current_step < $step_count - 1) {
                        wp_redirect($this->get_wizard_step_url($this->get_current_post_id(), $current_step + 1));
                        exit;
                    }
                }

                // No more wizard steps, use the URL we are given if any
                $redirect_url = apply_filters('cuar/private-content/edit/after_' . $action . '/redirect_url',
                    $this->get_redirect_url_after_action(),
                    $this->get_slug());
                if ($redirect_url != null) {
                    wp_redirect($redirect_url);
                    exit;
                }
            }

            return true;
        }

        public function print_page_content($args = array(), $shortcode_content = '')
        {
            $action = $this->get_action();
            if (get_query_var('cuar_action', null) != null) {
                $action = get_query_var('cuar_action', null);
            }

            if ( !$this->is_action_authorized($action)) {
                return;
            }

            parent::print_page_content($args, $shortcode_content);
        }

        public function get_default_owners()
        {
            $legacy_owner_type = $this->plugin->get_option($this->get_edit_slug_for_options() . self::$OPTION_DEFAULT_OWNER_TYPE);
            $owners = $this->plugin->get_option($this->get_edit_slug_for_options() . self::$OPTION_DEFAULT_OWNER);

            // Handle old style option
            if (!empty($legacy_owner_type)) {
                $owners = array($legacy_owner_type => $owners);
            }

            return $owners;
        }

        public function get_default_category()
        {
            return $this->plugin->get_option($this->get_edit_slug_for_options() . self::$OPTION_DEFAULT_CATEGORY);
        }

        private function get_edit_slug_for_options() {
            if (strstr($this->get_slug(), 'update')) {
                return str_replace('update', 'new', $this->get_slug());
            }

            return $this->get_slug();
        }

        protected abstract function is_action_authorized($action);

        /**
         * The slug to redirect to after the form has been submitted
         */
        protected abstract function get_redirect_url_after_action();

        protected function do_edit_content($action, $form_data)
        {
            return false;
        }

        protected function get_required_fields()
        {
            if ($this->required_fields == null) {
                $this->required_fields = apply_filters(
                    'cuar/private-content/edit/required-fields?post_type=' . $this->get_friendly_post_type(),
                    $this->get_default_required_fields());
            }

            return $this->required_fields;
        }

        protected function get_default_required_fields()
        {
            return array();
        }

        protected function is_field_required($id)
        {
            $rf = $this->get_required_fields();

            return in_array($id, $rf);
        }

        protected $required_fields = null;

        protected function check_submitted_title($form_data, $error_message)
        {
            if (isset($form_data['cuar_title']) && !empty($form_data['cuar_title'])) {
                return $form_data['cuar_title'];
            }

            if ( !$this->is_field_required('cuar_title')) {
                return '';
            }

            $this->form_errors[] = new WP_Error('missing_title', $error_message);

            return false;
        }

        protected function check_submitted_content($form_data, $error_message)
        {
            if (isset($form_data['cuar_content']) && !empty($form_data['cuar_content'])) {
                return $form_data['cuar_content'];
            }

            if ( !$this->is_field_required('cuar_content')) {
                return '';
            }

            $this->form_errors[] = new WP_Error('missing_content', $error_message);

            return false;
        }

        protected function check_submitted_category($form_data, $error_message)
        {
            if (isset($form_data['cuar_category']) && !empty($form_data['cuar_category'])) {
                return $form_data['cuar_category'];
            }

            if ( !$this->is_field_required('cuar_category')) {
                return 0;
            }

            $this->form_errors[] = new WP_Error('missing_category', $error_message);

            return false;
        }

        protected function check_submitted_file($form_data, $error_message)
        {
            if (isset($_FILES) && isset($_FILES['cuar_file']) && !empty($_FILES['cuar_file']['name'])) {
                return $_FILES['cuar_file'];
            }

            if ( !$this->is_field_required('cuar_file')) {
                return null;
            }

            $this->form_errors[] = new WP_Error('missing_file', $error_message);

            return false;
        }

        protected function check_submitted_owners($form_data, $error_message)
        {
            /** @var CUAR_PostOwnerAddOn $po_addon */
            $po_addon = $this->plugin->get_addon('post-owner');
            $new_owner = $po_addon->get_owners_from_post_data();

            if ($new_owner != null && !empty($new_owner)) {
                return $new_owner;
            }

            if ( !$this->is_field_required('cuar_owner')) {
                return array();
            }

            $this->form_errors[] = new WP_Error('missing_owner', $error_message);

            return false;
        }

        /**
         * @return string The default status for the post when it gets created
         */
        protected function get_default_publish_status()
        {
            return 'publish';
        }

        /*------- WIZARD FUNCTIONS --------------------------------------------------------------------------------------*/

        /**
         * Allow this page to get URLs for the wizard steps
         */
        protected function enable_wizard_permalinks()
        {
            add_filter('rewrite_rules_array', array(&$this, 'insert_wizard_rewrite_rules'));
            add_filter('query_vars', array(&$this, 'insert_wizard_query_vars'));
        }

        /**
         * Add rewrite rules for the wizard steps
         *
         * @param array $rules
         *
         * @return array
         */
        public function insert_wizard_rewrite_rules($rules)
        {
            $page_id = $this->get_page_id();
            $page_slug = $this->get_full_page_path();

            $new_rules = array();

            // We need post ID + step number
            $rewrite_rule = 'index.php?page_id=' . $page_id . '&cuar_post_id=$matches[1]&cuar_wizard_step=$matches[2]';
            $rewrite_regex = $page_slug . '/([0-9]+)/([0-9]+)/?$';
            $new_rules[$rewrite_regex] = $rewrite_rule;

            return $new_rules + $rules;
        }

        /**
         * Add query variables for the wizard steps
         *
         * @param array $vars
         *
         * @return array
         */
        public function insert_wizard_query_vars($vars)
        {
            array_push($vars, 'cuar_post_id');
            array_push($vars, 'cuar_wizard_step');

            return $vars;
        }

        /**
         * The URL for a particular step of the wizard
         *
         * @param int $edited_post_id
         * @param int $step
         *
         * @return int The number of steps
         */
        protected function get_wizard_step_url($edited_post_id, $step)
        {
            if ($edited_post_id <= 0
                || $step < 0
                || $step >= $this->get_wizard_step_count()
            ) {
                return $this->get_page_url();
            }

            return trailingslashit($this->get_page_url()) . $edited_post_id . '/' . $step;
        }

        /**
         * The number of steps for the edition process
         *
         * @return int The number of steps
         */
        protected function get_wizard_step_count()
        {
            return 1;
        }

        /**
         * @return int The current step for the wizard
         */
        protected function get_current_wizard_step()
        {
            $step = (int)get_query_var('cuar_wizard_step', 0);
            if ( !is_int($step)) return 0;
            if ($step > $this->get_wizard_step_count()) return $this->get_wizard_step_count();

            return $step;
        }

        /**
         * Get the steps of the wizard in the form of an array with keys:
         *
         * - label: string The label to show
         *
         * @return array The steps
         */
        public function get_wizard_steps()
        {
            return array();
        }

        /*------- EDIT FORM ---------------------------------------------------------------------------------------------*/

        protected function set_form_success($title, $message, $actions = array())
        {
            $this->form_success = array(
                'title'   => $title,
                'message' => $message,
                'actions' => $actions
            );

            $this->should_print_form = false;
        }

        public function should_print_form()
        {
            if ( !empty($this->form_success)) {
                $title = $this->form_success['title'];
                $message = $this->form_success['message'];
                $actions = $this->form_success['actions'];

                include($this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-classes',
                    array(
                        'edit-content-form-success-' . $this->get_slug() . '.template.php',
                        'edit-content-form-success.template.php'
                    ),
                    'templates'));
            }

            return $this->should_print_form;
        }

        public function print_form_header()
        {
            if ($this->get_wizard_step_count() > 1) {
                $form_url = $this->get_wizard_step_url($this->get_current_post_id(), $this->get_current_wizard_step());
            } else {
                $form_url = $this->get_page_url();
            }

            printf('<form name="%1$s" method="post" class="cuar-form cuar-%3$s-content-form cuar-%1$s-form clearfix steps-icons steps-numbers steps-justified steps-left steps-arrows" action="%2$s" enctype="multipart/form-data">',
                $this->get_slug(),
                $form_url,
                $this->get_action());

            printf('<input type="hidden" name="cuar_form_id" value="%1$s" />', $this->get_slug());
            printf('<input type="hidden" name="cuar_post_type" value="%1$s" />', $this->get_friendly_post_type());

            if ($this->get_current_post_id() > 0) {
                printf('<input type="hidden" name="cuar_post_id" value="%1$s" />', $this->get_current_post_id());
            }

            if ( !isset($_POST['cuar_post_type'])) {
                $_POST['cuar_post_type'] = $this->get_friendly_post_type();
            }

            wp_nonce_field('cuar_' . $this->get_slug(), 'cuar_' . $this->get_slug() . '_nonce');

            do_action('cuar/private-content/edit/before_submit_errors', $this);

            if ( !empty($this->form_errors)) {
                foreach ($this->form_errors as $error) {
                    if (is_wp_error($error)) {
                        printf('<p class="alert alert-warning">%s</p>', $error->get_error_message());
                    } else if ($error !== false && !empty($error) && !is_array($error)) {
                        printf('<p class="alert alert-info">%s</p>', $error);
                    }
                }
            }

            do_action('cuar/private-content/edit/after_submit_errors', $this);

            do_action('cuar/private-content/edit/before_fields', $this);
        }

        public function print_form_footer()
        {
            do_action('cuar/private-content/edit/after_fields', $this);

            echo '</form>';
        }

        public function print_submit_button($field_label, $step_id)
        {
            do_action('cuar/private-content/edit/before_submit_button', $this, $step_id);

            /** @noinspection PhpUnusedLocalVariableInspection */
            $field_name = 'cuar_do_register';

            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-classes',
                'edit-content-form-field-submit.template.php',
                'templates'));

            do_action('cuar/private-content/edit/after_submit_button', $this, $step_id);
        }

	    public function print_submit_disabled_button($field_label, $step_id)
	    {
		    do_action('cuar/private-content/edit/before_submit_disabled_button', $this, $step_id);

		    include($this->plugin->get_template_file_path(
			    CUAR_INCLUDES_DIR . '/core-classes',
			    'edit-content-form-field-submit-disabled.template.php',
			    'templates'));

		    do_action('cuar/private-content/edit/after_submit_disabled_button', $this, $step_id);
	    }

        public function print_title_field($label, $help_text = '')
        {
            $title = '';
            if (isset($_POST['cuar_title'])) {
                $title = $_POST['cuar_title'];
            } else if ($this->get_current_post() != null) {
                $title = $this->get_current_post()->post_title;
            }

            $field_code = sprintf('<input type="text" id="cuar_title" name="cuar_title" value="%1$s" class="form-control" />', esc_attr($title));
            $this->print_form_field('cuar_title', $label, $field_code, $help_text);
        }

        public function print_file_field($label, $help_text = '')
        {
            $field_code = '<input type="file" id="cuar_file" name="cuar_file" class="form-control" />';
            $this->print_form_field('cuar_file', $label, $field_code, $help_text);
        }

        public function print_add_attachment_method_browser($post_id)
        {
            wp_enqueue_script('cuar.frontend');

            /** @var CUAR_PrivateFileAddOn $pf_addon */
            $pf_addon = $this->plugin->get_addon('private-files');
            $pf_addon->print_add_attachment_method_browser($post_id);
        }

        public function print_attachment_manager_scripts()
        {
            wp_enqueue_script('cuar.frontend');

            /** @var CUAR_PrivateFileAddOn $pf_addon */
            $pf_addon = $this->plugin->get_addon('private-files');
            $pf_addon->print_attachment_manager_scripts();
        }

        public function print_current_attachments_manager($post_id)
        {
            /** @var CUAR_PrivateFileAddOn $pf_addon */
            $pf_addon = $this->plugin->get_addon('private-files');
            $pf_addon->print_current_attachments_manager($post_id);
        }

        public function print_content_field($label, $help_text = '')
        {
            $content = '';
            $id = -1;
            if (isset($_POST['cuar_content'])) {
                $content = $_POST['cuar_content'];
            } else if ($this->get_current_post() != null) {
                $content = $this->get_current_post()->post_content;
                $id = $this->get_current_post_id();
            }

	        if ( ! $this->is_rich_editor_enabled() ) {
		        $field_code = sprintf( '<textarea rows="5" cols="40" name="cuar_content" id="cuar_content" class="form-control">%1$s</textarea>',
			        esc_attr( $content ) );
	        } else {
		        $field_code = sprintf( '<input type="hidden" id="cuar_post_type" name="cuar_post_type" value="%1$s">'
		                               . '<input type="hidden" id="cuar_post_id" name="cuar_post_id" value="%2$s">'
		                               . '%3$s'
		                               . '<textarea rows="5" cols="40" name="cuar_content" id="cuar_content" class="form-control cuar-js-richeditor">%4$s</textarea>',
			        $this->get_friendly_post_type(),
			        $id,
			        wp_nonce_field( 'cuar_insert_image', 'cuar_insert_image_nonce' ),
			        esc_attr( $content ));
	        }

            $this->print_form_field('cuar_content', $label, $field_code, $help_text);
        }

        public function print_owner_field($label, $help_text = '')
        {
            if ($this->current_user_can_select_owner()) {
                /** @var CUAR_PostOwnerAddOn $po_addon */
                $po_addon = $this->plugin->get_addon('post-owner');
                $owners = $po_addon->get_owners_from_post_data();

                if (empty($owners) && $this->get_current_post() != null) {
                    $owners = $po_addon->get_post_owners($this->get_current_post_id());
                }

                ob_start();
                $po_addon->print_owner_fields($owners);
                $field_code = ob_get_contents();
                ob_end_clean();

                $this->print_form_field('cuar_owner', $label, $field_code, $help_text);
            } else {
                $owners = $this->get_default_owners();

                ob_start();
                /** @var CUAR_PostOwnerAddOn $po_addon */
                $po_addon = $this->plugin->get_addon('post-owner');
                $po_addon->print_owner_fields_readonly($owners);
                $field_code = ob_get_contents();
                ob_end_clean();

                echo $field_code;
            }
        }

        public function print_category_field($label, $help_text = '')
        {
            $categories = get_terms($this->get_friendly_taxonomy(), array(
                'hide_empty' => false,
                'fields'     => 'count'
            ));
            if (empty($categories)) {
                $field_code = '<input type="hidden" name="cuar_category" value="-1" />';
                echo $field_code;
            } else if ($this->current_user_can_select_category()) {
                $category = -1;
                if (isset($_POST['cuar_category'])) {
                    $category = $_POST['cuar_category'];
                } else if ($this->get_current_post() != null) {
                    $cats = wp_get_post_terms($this->get_current_post_id(), $this->get_friendly_taxonomy(), array('fields' => 'ids'));
                    $category = implode(',', $cats);
                }

                $field_code = wp_dropdown_categories(array(
                    'taxonomy'     => $this->get_friendly_taxonomy(),
                    'name'         => 'cuar_category',
                    'hide_empty'   => 0,
                    'hierarchical' => 1,
                    'selected'     => $category,
                    'orderby'      => 'NAME',
                    'echo'         => false,
                    'class'        => 'cuar-js-select-single form-control',
                ));

                $this->print_form_field('cuar_category', $label, $field_code, $help_text);
            } else {
                $category = $this->get_default_category();
                $field_code = sprintf('<input type="hidden" name="cuar_category" value="%1$s" />', esc_attr($category));
                echo $field_code;
            }
        }

        /**
         * Print a field for the edit form
         *
         * @param string $field_name
         * @param string $field_label
         * @param string $field_code
         * @param string $field_help_text
         */
        public function print_form_field($field_name, $field_label, $field_code, $field_help_text = '')
        {
            do_action('cuar/private-content/edit/before_field?id=' . $field_name, $this);

            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-classes',
                array(
                    'edit-content-form-field-wrapper-' . $field_name . '.template.php',
                    'edit-content-form-field-wrapper.template.php'
                ),
                'templates'));

            do_action('cuar/private-content/edit/after_field?id=' . $field_name, $this);
        }

        public function set_current_post_id($post_id)
        {
            $this->current_post_id = $post_id;
        }

        public function get_current_post_id()
        {
            if ($this->current_post_id == null) {
                if (isset($_POST['cuar_post_id'])) {
                    $this->current_post_id = $_POST['cuar_post_id'];
                } else if (get_query_var('cuar_post_id', 0) > 0) {
                    $this->current_post_id = get_query_var('cuar_post_id', 0);
                } else if (get_query_var('cuar_post_name', null) != null) {
                    $args = array(
                        'name'      => get_query_var('cuar_post_name', null),
                        'post_type' => array($this->get_friendly_post_type())
                    );

                    if (get_query_var('year', null) != null) {
                        $args['year'] = get_query_var('year', null);
                    }
                    if (get_query_var('monthnum', null) != null) {
                        $args['monthnum'] = get_query_var('monthnum', null);
                    }
                    if (get_query_var('day', null) != null) {
                        $args['day'] = get_query_var('day', null);
                    }

                    $post = get_posts($args);

                    if ( !empty($post)) {
                        $this->current_post_id = $post[0]->ID;
                        $this->current_post = $post[0];
                    } else {
                        $this->current_post_id = 0;
                    }
                } else {
                    $this->current_post_id = 0;
                }
            }

            return $this->current_post_id;
        }

        public function get_current_post()
        {
            if ($this->current_post == null && $this->get_current_post_id() > 0) {
                $this->current_post = get_post($this->get_current_post_id());
            }

            return $this->current_post;
        }

	    /*------- AJAX HANDLING -----------------------------------------------------------------------------------------*/

	    /**
	     * Ajax function used to delete an image into the rich editor
	     */
	    public function ajax_delete_image()
	    {
		    // Check nonce
		    check_ajax_referer( 'cuar_insert_image', 'nonce' );

		    // Prepare datas
		    $posted_data     = isset( $_POST ) ? $_POST : null;
		    $file_data       = isset( $_FILES ) ? $_FILES : null;
		    $data            = array_merge( $posted_data, $file_data );
		    $post_type       = isset( $data['post_type'] ) ? $data['post_type'] : null;
		    $post_id         = isset( $data['post_id'] ) ? $data['post_id'] : null;
		    $current_user_id = get_current_user_id();

		    // Check permissions
		    $this->ajax_is_user_allowed_to_create_or_update_content( $post_type, $post_id, $current_user_id );

		    // Check file selected for deletion
		    if ( empty( $data['name'] ) ) {
			    wp_send_json_error( __( 'No file has been selected for deletion', 'cuar' ) );
		    }

		    // Check user
		    $user_check = get_userdata( (int) $data['author'] );
		    if ( $user_check === false ) {
			    wp_send_json_error( __( 'Oops! Security check failed!', 'cuar' ) );
		    }

		    // Check hash
		    $hash_check = md5( $data['subdir'] . $user_check->data->user_login );
		    if ( $hash_check !== $data['hash'] ) {
			    wp_send_json_error( __( 'Oops! Security check failed!', 'cuar' ) );
		    }

		    // Suppress parent dots
		    $data['subdir'] = ltrim( $data['subdir'], '/.' );
		    $data['name']   = ltrim( $data['name'], '/.' );

		    // Reconstruct image path
		    $upload_locations = $this->ajax_custom_editor_images_upload_dir();
		    $file_to_delete   = $upload_locations['basedir']
		                        . apply_filters( 'cuar/private-content/editor-images/subdir-upload-location', '/customer-area/' )
		                        . $data['subdir'] . '/' . $data['name'];

		    // Check if file exists
		    if ( ! file_exists( $file_to_delete ) ) {
			    wp_send_json_error( __( 'It looks like the file you tried to delete does not exists.', 'cuar' ) );
		    }

		    // Check file type
		    $supported_types = apply_filters( 'cuar/private-content/editor-images/supported-types',
			    array(
				    'image/jpeg',
				    'image/gif',
				    'image/png'
			    ) );
		    $arr_file_type   = wp_check_filetype( basename( $file_to_delete ) );
		    $uploaded_type   = $arr_file_type['type'];
		    if ( ! in_array( $uploaded_type, $supported_types, true ) ) {
			    wp_send_json_error( sprintf( __( 'This file type is not allowed. You can only delete: %s', 'cuar' ), implode( ', ', $supported_types ) ) );
		    }

		    // Delete file
		    if ( ! unlink( $file_to_delete ) ) {
			    wp_send_json_error( __( 'This file cannot be deleted, please contact site administrator.', 'cuar' ) );
		    } else {
			    wp_send_json_success();
		    }
	    }

	    /**
	     * Ajax function used to insert an image into the rich editor
	     */
	    public function ajax_insert_image()
	    {
		    // Check nonce
		    check_ajax_referer( 'cuar_insert_image', 'nonce' );

		    // Prepare datas
		    $posted_data     = isset( $_POST ) ? $_POST : null;
		    $file_data       = isset( $_FILES ) ? $_FILES : null;
		    $data            = array_merge( $posted_data, $file_data );
		    $post_type       = isset( $data['post_type'] ) ? $data['post_type'] : null;
		    $post_id         = isset( $data['post_id'] ) ? $data['post_id'] : null;
		    $current_user    = wp_get_current_user();
		    $current_user_id = get_current_user_id();

		    // Check permissions
		    $this->ajax_is_user_allowed_to_create_or_update_content( $post_type, $post_id, $current_user_id );

		    // Check uploaded file
		    if ( empty( $file_data ) ) {
			    wp_send_json_error( __( 'No file has been uploaded', 'cuar' ) );
		    }

		    // Check file type
		    $supported_types = apply_filters( 'cuar/private-content/editor-images/supported-types',
			    array(
				    'image/jpeg',
				    'image/gif',
				    'image/png'
			    ) );
		    $arr_file_type   = wp_check_filetype( basename( $data['file']['name'] ) );
		    $uploaded_type   = $arr_file_type['type'];
		    if ( ! in_array( $uploaded_type, $supported_types, true ) ) {
			    wp_send_json_error( sprintf( __( 'This file type is not allowed. You can only upload: %s', 'cuar' ), implode( ', ', $supported_types ) ) );
		    }

		    // Set custom upload dir
		    add_filter( 'upload_dir', array( &$this, 'ajax_custom_editor_images_upload_dir' ) );
		    $upload_result = wp_handle_upload( $data['file'], array( 'test_form' => false ) );
		    remove_filter( 'upload_dir', array( &$this, 'ajax_custom_editor_images_upload_dir' ) );

		    // Send results
		    if ( $upload_result && ! isset( $upload_result['error'] ) ) {
			    $subdir = apply_filters( 'cuar/private-content/editor-images/userdir-upload-location?user-login=' . $current_user->user_login, md5( $current_user->user_login ) );
			    wp_send_json_success( array(
				    'name'   => basename( $upload_result['url'] ),
				    'url'    => $upload_result['url'],
				    'subdir' => $subdir,
				    'hash'   => md5( $subdir . $current_user->user_login ),
				    'author' => $current_user_id
			    ) );
		    } else {
			    wp_send_json_error( sprintf( __( 'An error happened while uploading your file: %s', 'cuar' ), $upload_result['error'] ) );
		    }
	    }

	    /**
	     * Ajax function used to check if the current user is allowed to update or create content when inserting or deleting an image
	     */
	    public function ajax_is_user_allowed_to_create_or_update_content( $post_type, $post_id, $current_user_id )
	    {
		    // Check create content permissions
		    if ( empty( $post_type ) || ( ! empty( $post_id ) && $post_id < 0 && ! current_user_can( $post_type . '_create_content' ) ) ) {
			    wp_send_json_error( __( 'It looks like you are not allowed to create content for this kind of post type.', 'cuar' ) );
		    }

		    // Check update any content permissions
		    if ( empty( $post_type ) || ( ! empty( $post_id ) && $post_id > 0 && current_user_can( $post_type . '_update_any_content' ) !== true ) ) {

			    // Make sure this is an updated content
			    if ( ! empty( $post_id ) && $post_id > 0 ) {

				    // Check update authored content permissions
				    if ( $current_user_id === (int) get_post_field( 'post_author', $post_id ) && current_user_can( $post_type . '_update_authored_content' ) !== true ) {
					    wp_send_json_error( __( 'It looks like you are not allowed to update authored content for this post.', 'cuar' ) );
				    }

				    // Check update owned content permissions
				    $po_addon = $this->plugin->get_addon( 'post-owner' );
				    if ( $po_addon->is_user_owner_of_post( $post_id, $current_user_id ) && current_user_can( $post_type . '_update_owned_content' ) !== true ) {
					    wp_send_json_error( __( 'It looks like you are not allowed to update owned content for this post.', 'cuar' ) );
				    }
			    }
		    }
	    }

	    /**
	     * Change the upload directory on the fly when uploading our private file
	     *
	     * @return array
	     */
	    public function ajax_custom_editor_images_upload_dir()
	    {
		    remove_filter( 'upload_dir', array( &$this, 'ajax_custom_editor_images_upload_dir' ) );

		    $current_user        = wp_get_current_user();
		    $wp_upload_locations = apply_filters( 'cuar/private-content/editor-images/base-upload-location', wp_upload_dir() );

		    $dir = $wp_upload_locations['basedir'];
		    $url = $wp_upload_locations['baseurl'];

		    $subdir = apply_filters( 'cuar/private-content/editor-images/subdir-upload-location', '/customer-area/' );
		    $subdir .= apply_filters( 'cuar/private-content/editor-images/userdir-upload-location?user-login=' . $current_user->user_login, md5( $current_user->user_login ) );

		    $dir .= $subdir;
		    $url .= $subdir;

		    if ( ! file_exists( $dir ) && ! wp_mkdir_p( $dir ) ) {
			    wp_send_json_error( sprintf( __( 'An error happened while creating the folder: %s', 'cuar' ), $subdir ) );
		    }

		    $custom_dir = array(
			    'path'    => $dir,
			    'url'     => $url,
			    'subdir'  => $subdir,
			    'basedir' => $wp_upload_locations['basedir'],
			    'baseurl' => $wp_upload_locations['baseurl'],
			    'error'   => false,
		    );

		    return $custom_dir;
	    }

        /*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/

        public function is_rich_editor_enabled()
        {
            return $this->plugin->get_option($this->get_slug() . self::$OPTION_ENABLE_RICH_EDITOR);
        }

        /**
         * Set the default values for the options
         *
         * @param array $defaults
         *
         * @return array
         */
        public function set_default_options($defaults)
        {
            $defaults = parent::set_default_options($defaults);

            $slug = $this->get_slug();

            $defaults[$slug . self::$OPTION_ENABLE_RICH_EDITOR] = true;

            return $defaults;
        }

        /*------- CAPABILITIES ------------------------------------------------------------------------------------------*/

        public function get_configurable_capability_groups($capability_groups)
        {
            $post_type = $this->get_friendly_post_type();

            if (isset($capability_groups[$post_type])) {
                $capability_groups[$post_type]['groups']['edit-content'] = array(
                    'group_name'   => __('Content edition (from front-office)', 'cuar'),
                    'capabilities' => array(
                        $post_type . '_create_select_owner'    => __('Select an owner (uses default else)', 'cuar'),
                        $post_type . '_create_select_category' => __('Select a category (uses default else)', 'cuar'),
                    )
                );
            }

            return $capability_groups;
        }

        public function current_user_can_select_category()
        {
            $post_type = $this->get_friendly_post_type();

            return current_user_can($post_type . '_create_select_category');
        }

        public function current_user_can_select_owner()
        {
            $post_type = $this->get_friendly_post_type();

            return current_user_can($post_type . '_create_select_owner');
        }

        /*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

        public function enable_settings($target_tab, $enabled_settings = array('rich-editor', 'default-ownership', 'default-category', 'moderation'))
        {
            $this->enabled_settings = $enabled_settings;

            if (is_admin() && !empty($this->enabled_settings)) {
                // Settings
                add_action('cuar/core/settings/print-settings?tab=' . $target_tab, array(&$this, 'print_settings'), 20, 2);
                add_filter('cuar/core/settings/validate-settings?tab=' . $target_tab, array(&$this, 'validate_options'), 20, 3);
            }
        }

        protected function get_settings_section()
        {
            return $this->get_slug() . '_' . $this->get_action() . '_content_frontend';
        }

        protected abstract function get_settings_section_title();

        /**
         * Add our fields to the settings page
         *
         * @param CUAR_Settings $cuar_settings The settings class
         */
        public function print_settings($cuar_settings, $options_group)
        {
            if (empty($this->enabled_settings)) return;

            $slug = $this->get_slug();

            add_settings_section(
                $this->get_settings_section(),
                $this->get_settings_section_title(),
                array(&$this, 'print_empty_section_info'),
                CUAR_Settings::$OPTIONS_PAGE_SLUG
            );

            if (in_array('rich-editor', $this->enabled_settings)) {
                add_settings_field(
                    $slug . self::$OPTION_ENABLE_RICH_EDITOR,
                    __('Rich Editor', 'cuar'),
                    array(&$cuar_settings, 'print_input_field'),
                    CUAR_Settings::$OPTIONS_PAGE_SLUG,
                    $this->get_settings_section(),
                    array(
                        'option_id'     => $slug . self::$OPTION_ENABLE_RICH_EDITOR,
                        'type'          => 'checkbox',
                        'default_value' => 1,
                        'after'         => __('Enable the rich editor when editing content.', 'cuar')
                    )
                );
            }

            $this->print_additional_settings($cuar_settings, $options_group);
        }

        /**
         * Validate our options
         *
         * @param CUAR_Settings $cuar_settings
         * @param array         $input
         * @param array         $validated
         *
         * @return array
         */
        public function validate_options($validated, $cuar_settings, $input)
        {
            $slug = $this->get_slug();

            if (in_array('rich-editor', $this->enabled_settings)) {
                $cuar_settings->validate_boolean($input, $validated, $slug . self::$OPTION_ENABLE_RICH_EDITOR);
            }

            $validated = $this->validate_additional_settings($validated, $cuar_settings, $input);

            return $validated;
        }

        protected function print_additional_settings($cuar_settings, $options_group)
        {
        }

        protected function validate_additional_settings(&$validated, $cuar_settings, $input)
        {
            return $validated;
        }

        public function print_empty_section_info()
        {
        }

        public static $OPTION_ENABLE_RICH_EDITOR = '-enable_rich_editor';
        public static $OPTION_DEFAULT_OWNER_TYPE = '-default_owner_type';
        public static $OPTION_DEFAULT_OWNER = '-default_owner';
        public static $OPTION_DEFAULT_CATEGORY = '-default_category';

        protected $enabled_settings = array();

        /*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

        /**
         * Enqueues the select script on the user-edit and profile screens.
         */
        public function enqueue_scripts()
        {
            if (is_admin()) {
                $screen = get_current_screen();
                if (isset($screen->id) && $screen->id == 'customer-area_page_wpca-settings') {
                    $this->plugin->enable_library('jquery.select2');
                }
            } else {
                $this->plugin->enable_library('jquery.select2');
            }
        }
    }

endif; // CUAR_AbstractEditContentPageAddOn
