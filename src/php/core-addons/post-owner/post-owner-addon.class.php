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

require_once(CUAR_INCLUDES_DIR . '/core-classes/addon.class.php');

if ( !class_exists('CUAR_PostOwnerAddOn')) :

    /**
     * Add-on to provide all the stuff required to set an owner on a post type and include that post type in the
     * customer area.
     *
     * @author Vincent Prat @ MarvinLabs
     */
    class CUAR_PostOwnerAddOn extends CUAR_AddOn
    {
        public static $META_OWNER_QUERYABLE = 'cuar_owner_queryable';
        public static $META_OWNER_DISPLAYNAME = 'cuar_owner_displayname';
        public static $META_OWNER_SORTABLE_DISPLAYNAME = 'cuar_owner_sortable_displayname';

        /** @var array */
        private $owner_types = null;

        /** @var CUAR_PostOwnerAdminInterface */
        private $admin_interface;

        /** @var  CUAR_PostOwnerUserOwnerType */
        private $usr_owner_type;

        /**
         * CUAR_PostOwnerAddOn constructor.
         */
        public function __construct()
        {
            parent::__construct('post-owner', '4.0.0');
        }

        public function get_addon_name()
        {
            return __('Post Owner', 'cuar');
        }

        public function run_addon($plugin)
        {
            $this->usr_owner_type = new CUAR_PostOwnerUserOwnerType();

            if (is_admin()) {
                $this->admin_interface = new CUAR_PostOwnerAdminInterface($plugin, $this);
            } else {
                add_action('template_redirect', array(&$this, 'protect_single_post_access'));
            }

            add_action('cuar/core/on-plugin-update', array(&$this, 'plugin_version_upgrade'), 10, 2);
        }

        /*------- QUERY FUNCTIONS ---------------------------------------------------------------------------------------*/

        /**
         * Builds the meta query to check if a user owns a post
         *
         * @param int $user_id The user ID of the owner
         *
         * @return array See the meta query documentation on WP codex
         */
        public function get_meta_query_post_owned_by($user_id)
        {
            $user_id = apply_filters('cuar/core/ownership/content/meta-query/override-owner-id', $user_id);
            $base_meta_query = array(
                'relation' => 'OR',
            );

            return apply_filters('cuar/core/ownership/content/meta-query', $base_meta_query, $user_id, $this);
        }

        /**
         * Given an owner type and ID, get the meta query component (sub-array)
         *
         * @param string     $owner_type
         * @param string|int $owner_id
         *
         * @return array
         */
        public function get_owner_meta_query_component($owner_type, $owner_id)
        {
            return array(
                'key'     => self::$META_OWNER_QUERYABLE,
                'value'   => '|' . $owner_type . '_' . $owner_id . '|',
                'compare' => 'LIKE'
            );
        }

        /**
         * Given an owners array, get the meta query components (array of arrays)
         *
         * @param array $owners
         *
         * @return array
         *
         */
        public function get_owners_meta_query_component($owners)
        {
            $mq = array();
            foreach ($owners as $type => $ids) {
                foreach ($ids as $id) {
                    $mq[] = $this->get_owner_meta_query_component($type, $id);
                }
            }

            return $mq;
        }

        /*------- PRIVATE FILE STORAGE DIRECTORIES ----------------------------------------------------------------------*/

        /**
         * This is the base directory where we will store the user files
         *
         * @param bool $create_dirs Shall we create the directory if missing
         *
         * @return string
         */
        public function get_base_private_storage_directory($create_dirs = false)
        {
            $dir = WP_CONTENT_DIR . '/customer-area/storage';
            $dir = apply_filters('cuar/core/ownership/base-private-storage-directory', $dir);
            if ($create_dirs && !file_exists($dir)) {
                mkdir($dir, 0775, true);
            }

            return $dir;
        }

        /**
         * This is the base URL where we can access the user files directly (should be protected to forbid direct
         * downloads)
         *
         * @return string
         */
        public function get_base_private_storage_url()
        {
            return WP_CONTENT_URL . '/customer-area/storage';
        }

        /**
         * Get the absolute path to a private file.
         *
         * @param int    $post_id     The ID of the post which is assigned to an owner
         * @param string $filename    The name of the file
         * @param bool   $create_dirs Shall we create the directory if missing
         *
         * @return boolean|string
         */
        public function get_private_file_path($filename, $post_id, $create_dirs = false)
        {
            if (empty($post_id) || empty($filename)) {
                return false;
            }

            $dir = $this->get_base_private_storage_directory()
                . '/'
                . $this->get_private_storage_directory($post_id, false, false);

            if ($create_dirs && !file_exists($dir)) {
                mkdir($dir, 0775, true);
            }

            return $dir . '/' . $filename;
        }

        /**
         * Get a user's private storage directory. This directory is relative to the main upload directory
         *
         * @param int  $post_id     The ID of the post which is assigned to an owner
         * @param bool $absolute    Do we need the absolute path?
         * @param bool $create_dirs Shall we create the directory if missing
         *
         * @return bool|string The path
         */
        public function get_private_storage_directory($post_id, $absolute = false, $create_dirs = false)
        {
            if (empty($post_id)) {
                return false;
            }

            // Do something to make a directory out of the post_id
            $dir = md5('wpca-' . $post_id . md5(-$post_id * $post_id * $post_id));

            if ($absolute) {
                $dir = $this->get_base_private_storage_directory() . "/" . $dir;
            }

            if ($create_dirs && !file_exists($dir)) {
                mkdir($dir, 0775, true);
            }

            return $dir;
        }

        // region Deprecated support functions (change of storage directory structure in 6.2)

        /**
         * Support function for versions of WP Customer Area older than 6.2
         *
         * @param bool $create_dirs
         *
         * @return string
         * @deprecated
         */
        public function get_legacy_base_private_storage_directory($create_dirs = false)
        {
            $dir = WP_CONTENT_DIR . '/customer-area';
            $dir = apply_filters('cuar/core/ownership/base-private-storage-directory', $dir);
            if ($create_dirs && !file_exists($dir)) {
                mkdir($dir, 0775, true);
            }

            return $dir;
        }

        /**
         * Get a user's private storage directory. This directory is relative to the main upload directory
         *
         * @param int  $post_id     The ID of the post which is assigned to an owner
         * @param bool $absolute    Do we need the absolute path?
         * @param bool $create_dirs Shall we create the directory if missing
         *
         * @return bool|string The path
         * @deprecated
         */
        public function get_legacy_private_storage_directory($post_id, $absolute = false, $create_dirs = false)
        {
            if (empty($post_id)) {
                return false;
            }

            $owners = $this->get_post_owners($post_id);

            if (empty($owners)) return false;

            foreach ($owners as $type => $ids) {
                if ( !empty($ids)) {
                    return $this->get_legacy_owner_storage_directory($ids,
                        $type,
                        $absolute,
                        $create_dirs);
                }
            }

            return false;
        }

        /**
         * Get the absolute path to a private file.
         *
         * @param int    $post_id     The ID of the post which is assigned to an owner
         * @param string $filename    The name of the file
         * @param array  $owner_ids   The IDs of the owners
         * @param string $owner_type  The type of owner
         * @param bool   $create_dirs Shall we create the directory if missing
         *
         * @return bool|string
         *
         * @deprecated
         */
        public function get_legacy_owner_file_path($post_id, $filename, $owner_ids, $owner_type, $create_dirs = false)
        {
            if (empty($owner_ids) || empty($owner_type) || empty($filename)) {
                return false;
            }

            $dir = $this->get_legacy_base_private_storage_directory()
                . '/'
                . $this->get_legacy_owner_storage_directory($owner_ids, $owner_type);

            if ($create_dirs && !file_exists($dir)) {
                mkdir($dir, 0775, true);
            }

            return $dir . '/' . $filename;
        }

        /**
         * Get a user's private storage directory. This directory is relative to the main upload directory
         *
         * @param array  $owner_ids   The IDs of the owners
         * @param string $owner_type  The type of owner
         * @param bool   $absolute    Do we need the absolute path?
         * @param bool   $create_dirs Shall we create the directory if missing
         *
         * @return string The storage directory for an owner
         *
         * @deprecated
         */
        public function get_legacy_owner_storage_directory($owner_ids, $owner_type, $absolute = false, $create_dirs = false)
        {
            if (empty($owner_ids) || empty($owner_type)) {
                return false;
            }

            if (is_array($owner_ids)) {
                $dir = md5($owner_type . '_' . implode(',', $owner_ids));
            } else {
                $dir = md5($owner_type . '_' . $owner_ids);
            }

            if ($absolute) {
                $dir = $this->get_legacy_base_private_storage_directory() . "/" . $dir;
            }

            if ($create_dirs && !file_exists($dir)) {
                mkdir($dir, 0775, true);
            }

            return $dir;
        }

        /**
         * Get the absolute path to a private file.
         *
         * @param int    $post_id     The ID of the post which is assigned to an owner
         * @param string $filename    The name of the file
         * @param bool   $create_dirs Shall we create the directory if missing
         *
         * @return boolean|string
         * @deprecated
         */
        public function get_legacy_private_file_path($filename, $post_id, $create_dirs = false)
        {
            if (empty($post_id) || empty($filename)) {
                return false;
            }

            $dir = $this->get_legacy_base_private_storage_directory()
                . '/'
                . $this->get_legacy_private_storage_directory($post_id, false, false);

            if ($create_dirs && !file_exists($dir)) {
                mkdir($dir, 0775, true);
            }

            return $dir . '/' . $filename;
        }

        // endregion

        /*------- ACCESS TO OWNER INFO ----------------------------------------------------------------------------------*/

        /** Check if the given value is a valid owner type
         *
         * @param string $type The owner type to test
         *
         * @return bool true if the owner type seems correct
         */
        public function is_valid_owner_type($type)
        {
            $types = $this->get_owner_types();

            return array_key_exists($type, $types);
        }


        /**
         * Returns all the possible owner types in the form of an associative array. The key is the owner type (should
         * remain constant) and the value is a string to be displayed in various places (should be internationalised).
         *
         * @return mixed
         */
        public function get_owner_types()
        {
            if ($this->owner_types == null) {
                $this->owner_types = apply_filters('cuar/core/ownership/owner-types', array());
            }

            return $this->owner_types;
        }

        /**
         * List all the owners which can be selected for a given type
         *
         * @param $type_id
         *
         * @return array key is the owner ID, value is the text to show to the user
         */
        public function get_selectable_owners($type_id)
        {
            return apply_filters('cuar/core/ownership/printable-owners?owner-type=' . $type_id, array());
        }

        /**
         * Do we allow more than a single owner for the given type
         *
         * @param $type_id
         *
         * @return bool
         */
        public function is_multiple_selection_enabled($type_id)
        {
            return apply_filters('cuar/core/ownership/enable-multiple-select?owner-type=' . $type_id, false);
        }

        /**
         * Tell if this post type should be protected or not
         *
         * @param string $post_type
         * @param array  $private_types
         *
         * @return bool
         */
        public function is_post_type_protected($post_type, $private_types = null)
        {
            if ($private_types == null) {
                $private_types = $this->plugin->get_private_types();
            }

            $is_protected = isset($private_types[$post_type]) ? true : false;

            return apply_filters('cuar/core/ownership/is-post-type-protected', $is_protected, $post_type, $private_types);
        }

        /**
         * Tell if this post should be protected or not
         *
         * @param int    $post_id
         * @param string $post_type
         * @param array  $private_types
         *
         * @return bool
         */
        public function is_post_protected($post_id, $post_type = null, $private_types = null)
        {
            if ($post_type == null) {
                $post_type = get_post_type($post_id);
            }

            if ($private_types == null) {
                $private_types = $this->plugin->get_private_types();
            }

            $is_protected = isset($private_types[$post_type]) ? true : false;

            return apply_filters('cuar/core/ownership/is-post-protected', $is_protected, $post_id, $post_type, $private_types);
        }

        /**
         * Check if a user is an owner of the given post.
         *
         * @param int $post_id
         * @param int $user_id
         *
         * @return mixed|void
         */
        public function is_user_owner_of_post($post_id, $user_id)
        {
            $owners = $this->get_post_owners($post_id);
            foreach ($owners as $owner_type => $owner_ids) {
                $is_owner = apply_filters('cuar/core/ownership/validate-post-ownership', false, $post_id, $user_id, $owner_type, $owner_ids);
                if ($is_owner) return true;
            }

            return false;
        }

        /**
         * Get the name to be displayed
         *
         * @param int  $post_id The post ID
         * @param bool $prefix_with_type
         *
         * @return string the type of ownership (defaults to 'usr')
         */
        public function get_post_displayable_owners($post_id, $prefix_with_type = false)
        {
            if ($prefix_with_type) {
                $name = get_post_meta($post_id, self::$META_OWNER_SORTABLE_DISPLAYNAME, true);
                if ( !$name || empty($name)) {
                    $name = __('Unknown', 'cuar');
                }

                return apply_filters('cuar/core/ownership/sortable-displayname', $name, $post_id);
            } else {
                $name = get_post_meta($post_id, self::$META_OWNER_DISPLAYNAME, true);
                if ( !$name || empty($name)) {
                    $name = __('Unknown', 'cuar');
                }
                if ( !is_array($name)) {
                    $name = array($name);
                }

                return apply_filters('cuar/core/ownership/displayname', $name, $post_id);
            }
        }

        /**
         * @param $post_id
         *
         * @return array
         */
        public function get_post_owners($post_id)
        {
            $queryable_owners = get_post_meta($post_id, self::$META_OWNER_QUERYABLE, true);
            $owners = $this->decode_owners($queryable_owners);

            return apply_filters('cuar/core/ownership/post-owners', $owners, $post_id);
        }

        /**
         * Get the real user ids behind the logical owner of the post
         *
         * @param int $post_id The post ID
         *
         * @return array User ids
         */
        public function get_post_owner_user_ids($post_id)
        {
            $user_ids = array();

            $owners = $this->get_post_owners($post_id);
            foreach ($owners as $type => $ids) {
                $tmp = apply_filters('cuar/core/ownership/real-user-ids?owner-type=' . $type, array(), $ids);
                $user_ids = array_merge($user_ids, $tmp);
            }

            $user_ids = array_unique($user_ids);

            // Let other add-ons return what they want
            return $user_ids;
        }

        /**
         * Save the owner details for the given post
         *
         * @param int   $post_id The post ID
         * @param array $owners
         * @param bool  $ensure_types_exist
         */
        public function save_post_owners($post_id, $owners, $ensure_types_exist = true)
        {
            $owner_types = $this->get_owner_types();

            // Check owner type exists
            if ($ensure_types_exist && !empty($owners)) {
                foreach ($owners as $type => $ids) {
                    if ( !array_key_exists($type, $owner_types)) {
                        $this->plugin->add_admin_notice('Invalid owner type ' . $type . ', some add-on must be doing something wrong');

                        return;
                    }
                }
            }

            $previous_owners = $this->get_post_owners($post_id);

            // Other addons can do something before we save
            do_action("cuar/core/ownership/before-save-owner", $post_id, $previous_owners, $owners);

            // Serialize the owner ids for queries
            $queryable_ids = $this->encode_owners($owners);

            $displayname = array();
            $sortable_displayname = array();
            foreach ($owner_types as $type_id => $type_label) {
                if (empty($owners[$type_id])) continue;

                $displayname[$type_id] = apply_filters('cuar/core/ownership/saved-displayname', '', $post_id, $type_id, $owners[$type_id]);
                $sortable_displayname[$type_id] = apply_filters('cuar/core/ownership/saved-sortable-displayname', $type_label . ' - ' . $displayname[$type_id],
                    $post_id, $type_id, $owners[$type_id]);
            }
            $sortable_displayname = implode(' + ', $sortable_displayname);

            // Persist data
            update_post_meta($post_id, self::$META_OWNER_QUERYABLE, $queryable_ids);
            update_post_meta($post_id, self::$META_OWNER_DISPLAYNAME, $displayname);
            update_post_meta($post_id, self::$META_OWNER_SORTABLE_DISPLAYNAME, $sortable_displayname);

            // Other addons can do something after we save
            $new_owners = $this->get_post_owners($post_id);
            $post = get_post($post_id);
            do_action("cuar/core/ownership/after-save-owner", $post_id, $post, $previous_owners, $new_owners);
        }

        /**
         * @param int   $post_id
         * @param array $owners
         *
         * @return mixed|string|void
         */
        public function get_displayable_owners_for_log($post_id, $owners)
        {
            $owner_types = $this->get_owner_types();
            $sortable_display_names = array();

            foreach ($owner_types as $type_id => $type_label) {
                if (empty($owners[$type_id])) continue;

                $displayname = apply_filters('cuar/core/ownership/saved-displayname', '', $post_id, $type_id, $owners[$type_id]);
                $sortable_display_names[] = apply_filters('cuar/core/ownership/saved-sortable-displayname', $type_label . ' - ' . $displayname, $post_id,
                    $type_id, $owners[$type_id]);
            }

            return implode(' + ', $sortable_display_names);
        }

        /**
         * Encode an array of users/user groups for storage in the meta table. We expect a dictionnary where the keys are
         * user groups and values are arrays of user IDs.
         */
        private function encode_owners($owners)
        {
            $out = '';
            foreach ($owners as $type => $ids) {
                $ids = array_filter($ids);
                if (empty($ids)) continue;

                $sep = '|' . $type . '_';
                $out .= $sep . implode($sep, $ids);
            }
            $out .= '|';

            return $out;
        }

        /**
         * Decode an array of users/user groups from storage in the meta table.
         */
        private function decode_owners($raw)
        {
            $owners = array();
            $tokens = explode('|', $raw);
            foreach ($tokens as $t) {
                if (empty($t)) continue;

                $owner = explode('_', $t, 2);
                $type = $owner[0];
                $id = $owner[1];
                if (count($owner) != 2 || empty($type) || empty($id)) continue;

                if ( !isset($owners[$type])) $owners[$type] = array();

                $owners[$type][] = $id;
            }

            return $owners;
        }

        /*------- PRINT SELECTION FIELDS ---------------------------------------------------------------------------------------------------------------------*/

        public function print_owner_fields($owners, $field_prefix = 'cuar_owners_', $field_group = null)
        {
            $po_addon = $this;
            $owner_types = apply_filters('cuar/core/ownership/selectable-owner-types', null);
            if (null === $owner_types) $owner_types = $this->get_owner_types();

            $template_suffix = is_admin() ? '-admin' : '-frontend';

            wp_enqueue_script(is_admin() ? 'cuar.admin' : 'cuar.frontend');
            $print_javascript = false;
            $theme_support = get_theme_support('customer-area.library.jquery.select2');
            if (is_admin()
                || $theme_support === false
                || (is_array($theme_support) && !in_array('markup', $theme_support[0]))
            ) {
                $this->plugin->enable_library('jquery.select2');
                $print_javascript = true;
            }

            wp_nonce_field('cuar_save_owners', 'wp_cuar_nonce_owner');

            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/post-owner',
                array(
                    'post-owner-fields' . $template_suffix . '.template.php',
                    'post-owner-fields.template.php',
                )
            ));
        }

        public function print_owner_fields_readonly($owners, $field_prefix = 'cuar_owners_')
        {
            wp_nonce_field('cuar_save_owners', 'wp_cuar_nonce_owner');

            include($this->plugin->get_template_file_path(
                CUAR_INCLUDES_DIR . '/core-addons/post-owner',
                'post-owner-fields-readonly.template.php'
            ));
        }

        /**
         * Get the owner details (id and type) from HTTP POST data
         *
         * @param string $ids_field_prefix
         *
         * @return array
         */
        public function get_owners_from_post_data($ids_field_prefix = 'cuar_owners_')
        {
            $owners = array();

            $owner_types = $this->get_owner_types();
            foreach ($owner_types as $owner_type => $type_label) {
                $ids_field_name = $ids_field_prefix . $owner_type;

                // If no owner selected, just skip this type
                if ( !isset($_POST[$ids_field_name]) || empty($_POST[$ids_field_name])) continue;

                $owners[$owner_type] = is_array($_POST[$ids_field_name]) ? $_POST[$ids_field_name] : array($_POST[$ids_field_name]);
            }

            return $owners;
        }

        /*------- FRONTEND ----------------------------------------------------------------------------------------------*/

        /**
         * Protect access to single posts: only for author and owner.
         */
        public function protect_single_post_access()
        {
            $private_post_types = $this->plugin->get_content_post_types();

            // If not on a matching post type, we do nothing
            if (empty($private_post_types) || !is_singular($private_post_types)) {
                return;
            }

            // If post is not protected, we do nothing
            $post = get_queried_object();
            if ( !$this->is_post_protected($post->ID)) {
                return;
            }

            // If not logged-in, we ask for details
            if ( !is_user_logged_in()) {
                $this->plugin->login_then_redirect_to_url(get_permalink());
            }

            // If not authorized to view the page, we bail
            $author_id = $post->post_author;
            $current_user_id = apply_filters('cuar/core/ownership/protect-single-post/override-user-id',
                get_current_user_id());

            $is_current_user_owner = $this->is_user_owner_of_post($post->ID, $current_user_id);
            if ( !($is_current_user_owner || $author_id == $current_user_id
                || current_user_can('cuar_view_any_' . get_post_type()))
            ) {
                wp_die(__("You are not authorized to view this page", "cuar"));
                exit();
            }

            do_action('cuar/core/ownership/protect-single-post/on-access-granted', $post);
        }

        /*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/

        /**
         * When the plugin is upgraded
         *
         * @param string $from_version
         * @param string $to_version
         */
        // TODO PO REFACTORING
        // CAUTION
        // CLEANUP OLD CODE, 2.x NOT SUPPORTED ANYMORE ?
        // OR EXTERNALIZE TO ANOTHER FILE
        // USE version_compare !
        public function plugin_version_upgrade($from_version, $to_version)
        {
            // If upgrading from before 2.0.0 we must update the post meta fields
            if ($from_version < '2.0.0') {
                global $wpdb;

                // Find all existing owner ids
                $owner_metas = $wpdb->get_results($wpdb->prepare(
                    "SELECT meta_id, post_id, meta_key, meta_value "
                    . "FROM $wpdb->postmeta "
                    . "WHERE meta_key = %s",
                    'cuar_owner'));

                foreach ($owner_metas as $m) {
                    // Before 2.0.0 there was no owner type, so default to 'user'
                    $owner_type = 'user';
                    $owner_id = $m->meta_value;

                    // Add post meta (owner type, display names)
                    $u = new WP_User($owner_id);
                    $display_name = $u->display_name;
                    $sortable_display_name = sprintf(__('User - %s', 'cuar'), $u->display_name);

                    update_post_meta($m->post_id, self::$META_OWNER_TYPE, $owner_type);
                    update_post_meta($m->post_id, self::$META_OWNER_DISPLAYNAME, $display_name);
                    update_post_meta($m->post_id, self::$META_OWNER_SORTABLE_DISPLAYNAME, $sortable_display_name);
                    update_post_meta($m->post_id, self::$META_OWNER_QUERYABLE, $owner_type . '_' . $owner_id);

                    // If owner had a directory, rename that directory into the new naming scheme
                    $base_storage_directory = $this->get_base_private_storage_directory(true);
                    $new_name = $this->get_owner_storage_directory($owner_id, $owner_type);
                    $old_name = get_user_meta($owner_id, 'cuar_directory', true);

                    if ( !empty($old_name)) {
                        if (file_exists($base_storage_directory . "/" . $old_name)) {
                            rename($base_storage_directory . "/" . $old_name,
                                $base_storage_directory . "/" . $new_name);
                        }
                        delete_user_meta($owner_id, 'cuar_directory');
                    }
                }
            }

            if ($from_version < '2.3.0') {
                // Migrate all previous owner formats to the new one
                $posts_with_owner = get_posts(array(
                    'numberposts' => -1,
                    'post_status' => array(
                        'publish', 'auto-draft', 'future', 'draft', 'pending', 'private', 'inherit', 'trash'
                    ),
                    'post_type'   => array('cuar_private_page', 'cuar_private_file')
                ));

                foreach ($posts_with_owner as $p) {
                    $old_type = get_post_meta($p->ID, 'cuar_owner_type', true);
                    $old_owner = get_post_meta($p->ID, 'cuar_owner', true);
                    $old_path = $this->get_owner_storage_directory($old_owner, $old_type, true, false);

                    $new_type = null;
                    $new_owners = array($old_owner);

                    switch ($old_type) {
                        case 'user':
                            $new_type = 'usr';
                            break;
                        case 'user_group':
                            $new_type = 'grp';
                            break;
                        case 'role':
                            $new_type = 'rol';
                            break;
                    }

                    if ($new_type != null) {
                        $this->save_post_owners($p->ID, $new_owners, $new_type, false);
                        delete_post_meta($p->ID, 'cuar_owner');

                        $new_path = $this->get_owner_storage_directory($new_owners, $new_type, true, false);
                        if (file_exists($old_path)) {
                            rename($old_path, $new_path);
                        }
                    }
                }
            }
        }

    }

// Make sure the addon is loaded
    new CUAR_PostOwnerAddOn();

endif; // if (!class_exists('CUAR_PrivateFileAddOn')) 
