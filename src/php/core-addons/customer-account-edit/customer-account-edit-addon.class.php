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

if (!class_exists('CUAR_CustomerAccountEditAddOn')) :

    /**
     * Add-on to show the customer account edit page
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_CustomerAccountEditAddOn extends CUAR_AbstractPageAddOn
    {

        public function __construct()
        {
            parent::__construct('customer-account-edit');

            $this->set_page_parameters(820, [
                    'slug'                => 'customer-account-edit',
                    'parent_slug'         => 'customer-account-home',
                    'required_capability' => 'cuar_edit_account',
                ]
            );

            $this->set_page_shortcode('customer-area-account-edit');
        }

        public function get_label()
        {
            return __('Account - Edit', 'cuar');
        }

        public function get_title()
        {
            return __('Edit account', 'cuar');
        }

        public function get_hint()
        {
            return __('This page shows a form where the user can edit his account', 'cuar');
        }

        public function run_addon($plugin)
        {
            parent::run_addon($plugin);

            if (!is_admin())
            {
                add_action('template_redirect', [&$this, 'handle_form_submission']);
            }
        }

        public function get_page_addon_path()
        {
            return CUAR_INCLUDES_DIR . '/core-addons/customer-account-edit';
        }

        /*------- PAGE HANDLING -----------------------------------------------------------------------------------------*/

        public function handle_form_submission()
        {
            if (!$this->is_currently_displayed()) return false;

            if (!isset($_POST['cuar_form_id']) || $_POST['cuar_form_id'] != $this->get_slug()) return false;

            if (!wp_verify_nonce($_POST["cuar_" . $this->get_slug() . "_nonce"], 'cuar_' . $this->get_slug()))
            {
                die('An attempt to bypass security checks was detected! Please go back and try again.');
            }

            // If not logged-in, bail
            if (!is_user_logged_in()) return false;

            if (!$this->is_accessible_to_current_user())
            {
                die('You are not allowed to view this page.');
            }

            $current_user_id = get_current_user_id();

            $up_addon = $this->plugin->get_addon('user-profile');
            $fields = $up_addon->get_profile_fields();

            $this->form_errors = [];

            foreach ($fields as $field)
            {
                $res = $field->persist($current_user_id);
                if ($res !== true)
                {
                    if (is_array($res))
                    {
                        $this->form_errors = array_merge($this->form_errors, $res);
                    }
                    else
                    {
                        $this->form_errors[] = $res;
                    }
                }
            }

            /** @var CUAR_AddressesAddOn $ad_addon */
            $ad_addon = $this->plugin->get_addon('address-manager');
            $user_addresses = $ad_addon->get_registered_user_addresses();
            foreach ($user_addresses as $address_id => $address_label)
            {
                $address = isset($_POST[$address_id]) ? $_POST[$address_id] : [];
                $ad_addon->set_owner_address('usr', [$current_user_id], $address_id, $address);
            }

            do_action('cuar/core/user-profile/edit/save_profile_fields', $current_user_id, $this->form_errors, $_POST);

            if (empty($this->form_errors))
            {
                $cp_addon = $this->plugin->get_addon('customer-pages');
                $page_id = $cp_addon->get_page_id('customer-account');
                $redirect_url = get_permalink($page_id) . '?updated=1';
                wp_redirect($redirect_url);
                exit;
            }

            return true;
        }

        public function print_submit_button($label)
        {
            do_action('cuar/core/user-profile/edit/before_submit_button');

            echo '<div class="form-group">';
            echo '	<div class="submit-container">';
            echo '		<input type="submit" name="cuar_submit" value="' . esc_attr($label) . '" class="btn btn-default" />';
            echo '	</div>';
            echo '</div>';

            do_action('cuar/core/user-profile/edit/after_submit_button');
        }

        public function print_form_header()
        {
            printf('<form name="%1$s" method="post" class="cuar-form cuar-edit-account-form" action="%2$s" enctype="multipart/form-data">',
                $this->get_slug(), $this->get_page_url());

            printf('<input type="hidden" name="cuar_form_id" value="%1$s" />', $this->get_slug());

            wp_nonce_field('cuar_' . $this->get_slug(), 'cuar_' . $this->get_slug() . '_nonce');

            do_action('cuar/core/user-profile/edit/before_submit_errors');

            if (!empty($this->form_errors))
            {
                foreach ($this->form_errors as $error)
                {
                    printf('<p class="alert alert-warning">%s</p>', $error);
                }
            }

            do_action('cuar/core/user-profile/edit/after_submit_errors');
        }

        public function print_form_footer()
        {
            echo '</form>';
        }

        public function print_account_fields()
        {
            $current_user = get_userdata(get_current_user_id());

            $up_addon = $this->plugin->get_addon('user-profile');
            $groups = $up_addon->get_profile_field_groups();
            $fields = $up_addon->get_profile_fields();

            do_action('cuar/core/user-profile/edit/before_fields', $current_user);

            foreach ($groups as $group_id => $group_label)
            {
                $group_fields = array_filter($fields, function ($field) use ($group_id) {
                    if (empty($group_id) || $group_id === 'default')
                    {
                        return $field->get_arg('group') === '' || $field->get_arg('group') === 'default';
                    }

                    return $field->get_arg('group') === $group_id;
                });

                if (empty($group_fields))
                {
                    do_action('cuar/core/user-profile/edit/before_field_group?id=' . $group_id, $current_user);
                    do_action('cuar/core/user-profile/edit/after_field_group?id=' . $group_id, $current_user);
                    continue;
                }

                do_action('cuar/core/user-profile/edit/before_field_group?id=' . $group_id, $current_user);

                include($this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-addons/customer-account-edit',
                    [], 'templates', 'customer-account-edit-content-field-group-open.template.php'));

                foreach ($group_fields as $id => $field)
                {
                    do_action('cuar/core/user-profile/edit/before_field?id=' . $id, $current_user);

                    $field->render_form_field($current_user->ID);

                    do_action('cuar/core/user-profile/edit/after_field?id=' . $id, $current_user);
                }

                include($this->plugin->get_template_file_path(
                    CUAR_INCLUDES_DIR . '/core-addons/customer-account-edit',
                    [], 'templates', 'customer-account-edit-content-field-group-close.template.php'));

                do_action('cuar/core/user-profile/edit/after_field_group?id=' . $group_id, $current_user);
            }

            do_action('cuar/core/user-profile/edit/after_fields', $current_user);
        }

        public function print_address_fields()
        {
            $user = get_userdata(get_current_user_id());

            /** @var CUAR_AddressesAddOn $ad_addon */
            $ad_addon = $this->plugin->get_addon('address-manager');

            $user_addresses = $ad_addon->get_registered_user_addresses();
            foreach ($user_addresses as $address_id => $address_label)
            {
                $address = $ad_addon->get_owner_address('usr', [$user->ID], $address_id);
                $address_actions = [
                    'reset' => [
                        'label'   => __('Clear', 'cuar'),
                        'tooltip' => __('Clear the address', 'cuar'),
                    ],
                ];
                $extra_scripts = '';

                $ad_addon->print_address_editor($address,
                    $address_id, $address_label,
                    $address_actions, $extra_scripts, 'account');
            }
        }

        private $current_user = null;
    }

// Make sure the addon is loaded
    new CUAR_CustomerAccountEditAddOn();

endif; // if (!class_exists('CUAR_CustomerAccountEditAddOn')) :
