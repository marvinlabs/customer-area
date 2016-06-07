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

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon-edit-content-page.class.php');

if ( !class_exists('CUAR_AbstractCreateContentPageAddOn')) :

    /**
     * The base class for addons that should render a page to create private content
     *
     * @author Vincent Prat @ MarvinLabs
     */
    abstract class CUAR_AbstractCreateContentPageAddOn extends CUAR_AbstractEditContentPageAddOn
    {

        public function __construct($addon_id = null, $min_cuar_version = null)
        {
            parent::__construct($addon_id, $min_cuar_version);
        }

        public function get_action()
        {
            return 'create';
        }

        /*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

        protected function get_redirect_url_after_action()
        {
            // Let the user decide where he wants to go next
            return null;

            // Redirect to the main content page linked to this creation page
//            /** @var CUAR_CustomerPagesAddOn $cp_addon */
//            $cp_addon = $this->plugin->get_addon('customer-pages');
//            $page_id = $cp_addon->get_page_id($this->get_parent_slug());
//
//            return get_permalink($page_id);
        }

        protected function get_default_publish_status()
        {
            if ($this->is_moderation_enabled() && !$this->current_user_can_bypass_moderation())
            {
                return 'draft';
            }

            return parent::get_default_publish_status();
        }

        protected function get_default_required_fields()
        {
            return array('cuar_title', 'cuar_content', 'cuar_category', 'cuar_owner');
        }

        /*------- FORM HANDLING -----------------------------------------------------------------------------------------*/

        protected function is_action_authorized($action)
        {
            switch ($action)
            {
                case 'create':
                    // If not logged-in, bail
                    if ( !is_user_logged_in()) return false;

                    // User can create content
                    if ( !$this->current_user_can_create_content())
                    {
                        die(__('You are not allowed to create this type of content.', 'cuar'));
                    }

                    // There is a current post and we are not the author
                    $current_post = $this->get_current_post();
                    if ( !empty($current_post) && $current_post->post_author != get_current_user_id())
                    {
                        die(__('Trying to cheat?', 'cuar'));
                    }

                    // Current post type must match ours
                    if ( !empty($current_post) && $current_post->post_type!=$this->get_friendly_post_type())
                    {
                        die(__('Trying to cheat?', 'cuar'));
                    }

                    return true;
            }

            return false;
        }

        /*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/

        public function is_moderation_enabled()
        {
            return $this->plugin->get_option($this->get_slug() . self::$OPTION_ENABLE_MODERATION, false);
        }

        public function get_default_owners()
        {
            $legacy_owner_type = $this->plugin->get_option($this->get_slug() . self::$OPTION_DEFAULT_OWNER_TYPE);
            $owners = $this->plugin->get_option($this->get_slug() . self::$OPTION_DEFAULT_OWNER);

            // Handle old style option
            if (!empty($legacy_owner_type)) {
                $owners = array($legacy_owner_type => $owners);
            }

            return $owners;
        }

        public function get_default_category()
        {
            return $this->plugin->get_option($this->get_slug() . self::$OPTION_DEFAULT_CATEGORY, -1);
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

            $defaults[$slug . self::$OPTION_ENABLE_MODERATION] = false;
            $defaults[$slug . self::$OPTION_DEFAULT_OWNER] = array();
            $defaults[$slug . self::$OPTION_DEFAULT_CATEGORY] = -1;

            return $defaults;
        }

        /*------- CAPABILITIES ------------------------------------------------------------------------------------------*/

        public function get_configurable_capability_groups($capability_groups)
        {
            $capability_groups = parent::get_configurable_capability_groups($capability_groups);

            $post_type = $this->get_friendly_post_type();

            if (isset($capability_groups[$post_type]))
            {
                $capability_groups[$post_type]['groups']['create-content'] = array(
                    'group_name'   => __('Content creation (from front-office)', 'cuar'),
                    'capabilities' => array(
                        $post_type . '_create_content'           => __('Create content from front office', 'cuar'),
                        $post_type . '_create_bypass_moderation' => __('Bypass moderation (content is automatically published)', 'cuar')
                    )
                );
            }

            return $capability_groups;
        }

        public function is_accessible_to_current_user()
        {
            return $this->current_user_can_create_content();
        }

        public function current_user_can_create_content()
        {
            $post_type = $this->get_friendly_post_type();

            return current_user_can($post_type . '_create_content');
        }

        public function current_user_can_bypass_moderation()
        {
            $post_type = $this->get_friendly_post_type();

            return current_user_can($post_type . '_create_bypass_moderation');
        }

        /*------- SETTINGS PAGE -----------------------------------------------------------------------------------------*/

        protected function get_settings_section_title()
        {
            return __('Content creation', 'cuar');
        }

        protected function print_additional_settings($cuar_settings, $options_group)
        {
            parent::print_additional_settings($cuar_settings, $options_group);

            $slug = $this->get_slug();

            if (in_array('moderation', $this->enabled_settings))
            {
                add_settings_field(
                    $slug . self::$OPTION_ENABLE_MODERATION,
                    __('Moderation', 'cuar'),
                    array(&$cuar_settings, 'print_input_field'),
                    CUAR_Settings::$OPTIONS_PAGE_SLUG,
                    $this->get_settings_section(),
                    array(
                        'option_id'     => $slug . self::$OPTION_ENABLE_MODERATION,
                        'type'          => 'checkbox',
                        'default_value' => 1,
                        'after'         => __('Enable moderation when new content is submitted by a user.', 'cuar')
                            . '<p class="description">'
                            . __('An administrator will be required to review the content and publish it '
                                . 'manually. This can be used to moderate the content created by users by saving it as draft. '
                                . 'When content is saved as draft, it is not visible to anyone outside of the administration area. '
                                . 'You can allow some roles to bypass the moderation process by setting the corresponding capability. '
                                . 'This setting does not affect the backend interface.', 'cuar')
                            . '</p>'
                    )
                );
            }

            if (in_array('default-ownership', $this->enabled_settings))
            {
                add_settings_field(
                    $slug . self::$OPTION_DEFAULT_OWNER,
                    __('Default owners', 'cuar'),
                    array(&$cuar_settings, 'print_owner_select_field'),
                    CUAR_Settings::$OPTIONS_PAGE_SLUG,
                    $this->get_settings_section(),
                    array(
                        'option_id'            => $slug . self::$OPTION_DEFAULT_OWNER,
                        'owner_type_option_id' => $slug . self::$OPTION_DEFAULT_OWNER_TYPE,
                        'after'                => ''
                    )
                );
            }

            $tax = $this->get_friendly_taxonomy();
            if (in_array('default-category', $this->enabled_settings) && !empty($tax))
            {
                add_settings_field(
                    $slug . self::$OPTION_DEFAULT_CATEGORY,
                    __('Default category', 'cuar'),
                    array(&$cuar_settings, 'print_term_select_field'),
                    CUAR_Settings::$OPTIONS_PAGE_SLUG,
                    $this->get_settings_section(),
                    array(
                        'option_id' => $slug . self::$OPTION_DEFAULT_CATEGORY,
                        'taxonomy'  => $tax,
                        'after'     => ''
                    )
                );
            }
        }

        protected function validate_additional_settings(&$validated, $cuar_settings, $input)
        {
            $validated = parent::validate_additional_settings($validated, $cuar_settings, $input);

            $slug = $this->get_slug();

            if (in_array('moderation', $this->enabled_settings))
            {
                $cuar_settings->validate_boolean($input, $validated, $slug . self::$OPTION_ENABLE_MODERATION);
            }

            if (in_array('rich-editor', $this->enabled_settings))
            {
                $cuar_settings->validate_boolean($input, $validated, $slug . self::$OPTION_ENABLE_RICH_EDITOR);
            }

            if (in_array('default-ownership', $this->enabled_settings))
            {
                $cuar_settings->validate_owners($input, $validated, $slug . self::$OPTION_DEFAULT_OWNER, $slug . self::$OPTION_DEFAULT_OWNER_TYPE);
            }

            $tax = $this->get_friendly_taxonomy();
            if (in_array('default-category', $this->enabled_settings) && !empty($tax))
            {
                $cuar_settings->validate_term($input, $validated, $slug . self::$OPTION_DEFAULT_CATEGORY, $tax);
            }

            return $validated;
        }

        // Settings
        public static $OPTION_ENABLE_MODERATION = '-enable_moderation';
        public static $OPTION_DEFAULT_OWNER_TYPE = '-default_owner_type';
        public static $OPTION_DEFAULT_OWNER = '-default_owner';
        public static $OPTION_DEFAULT_CATEGORY = '-default_category';
    }

endif; // CUAR_AbstractCreateContentPageAddOn