<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

if ( !class_exists('CUAR_WordPressHelper')) :

    /**
     * Gathers some helper functions to facilitate some coding
     */
    class CUAR_WordPressHelper
    {

        /**
         * Get or create a role if it does not exist
         *
         * @param $id                   string The name of the role
         * @param $display_name         string The role name as shown to humans
         * @param $default_capabilities array The default capabilities to assign to the role if we have to create it
         *
         * @return WP_Role The role we created or fetched (if it existed before)
         */
        public static function getOrCreateRole($id, $display_name, $default_capabilities)
        {
            $role = add_role($id, $display_name, $default_capabilities);
            if (isset($role)) return $role;

            return get_role($id);
        }


        /**
         * Get the display name of a given role
         *
         * @param $id string The name of the role (typically $role->name)
         *
         * @return string The display name of the role, or the id if no display name is set
         */
        public static function getRoleDisplayName($id)
        {
            global $wp_roles;
            if ( !isset($wp_roles)) $wp_roles = new WP_Roles();

            return isset($wp_roles->role_names[$id])
                ? $wp_roles->role_names[$id]
                : $id;
        }

        /**
         * A user dropdown helper function.
         *
         * Similar to `wp_dropdown_users` function, but it is made for custom placeholder
         * attribute and for multiple dropdown. It's mainly used in creating and editing
         * projects.
         *
         * @since 0.1
         *
         * @param type $selected
         *
         * @return string
         */
        public static function getSelectUserDropdown($html_select_id, $hint, $all_users, $selected_users = array(), $multiple = true, $echo = true)
        {
            $multiple = $multiple ? ' multiple="true"' : '';
            $sel = ' selected="selected"';

            $options = array('<option></option>');
            if ($all_users)
            {
                foreach ($all_users as $user)
                {
                    if ( !($user instanceof WP_User))
                    {
                        $user = get_userdata($user);
                    }
                    $options[] = sprintf('<option value="%s"%s>%s</option>', $user->ID, in_array($user->ID, $selected_users) ? $sel : '', $user->display_name);
                }
            }

            $dropdown = '<select name="' . $html_select_id . '[]" id="' . $html_select_id . '" data-placeholder="' . $hint . '" ' . $multiple . '>';
            $dropdown .= implode("\n", $options);
            $dropdown .= '</select>';

            if ($echo) echo $dropdown;

            return $dropdown;
        }

// 	function getSelectTermDropdown( $html_select_id, $hint, $taxonomies, $get_terms_args, $selected_terms = array(), $multiple = true, $echo = true ){
// 		$multiple = $multiple ? ' multiple="true"' : '';
// 		$sel = ' selected="selected"';

// 		$options = array( '<option></option>' );

// 		$myterms = get_terms( $taxonomies, $get_terms_args );
// 		foreach ( $myterms as $term ) {
// 			$options[] = sprintf( '<option value="%s"%s>%s</option>', $term->ID, in_array( $term->ID, $selected_terms ) ? $sel : '', $term->name );
// 		}

// 		$dropdown = '<select name="' . $html_select_id . '[]" id="' . $html_select_id . '" data-placeholder="' . $hint . '" ' . $multiple . '>';
// 		$dropdown .= implode("\n", $options );
// 		$dropdown .= '</select>';

// 		if ( $echo ) echo $dropdown;

// 		return $dropdown;
// 	}


        /**
         * Convert a normal date string to unix date/time string
         *
         * @param string $date
         * @param string $src_format
         *
         * @return string
         */
        public static function convertDateToMysqlFormat($date, $src_format)
        {
            $myDateTime = DateTime::createFromFormat($src_format, $date);

            return $myDateTime->format('Y-m-d');
        }


        /**
         * Helper function for getting a date field from the database
         *
         * @param string $date
         *
         * @return int a timestamp on success, false otherwise
         */
        public static function convertDateFromMysqlFormat($date, $dest_format, $html_format = false)
        {
            $myDateTime = DateTime::createFromFormat('Y-m-d', $date);

            if ($html_format)
            {
                return sprintf('<time datetime="%1$s" title="%1$s">%2$s</time>', $myDateTime->format('c'), $myDateTime->format($dest_format));
            }
            else return $myDateTime->format($dest_format);
        }

        public static function ellipsis($text, $max = 100, $append = '&hellip;')
        {
            if (strlen($text) <= $max) return $text;
            $out = substr($text, 0, $max);
            if (strpos($text, ' ') === false) return $out . $append;

            return preg_replace('/\w+$/', '', $out) . $append;
        }
    }

endif; // class_exists CUAR_WordPressHelper