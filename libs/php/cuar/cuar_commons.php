<?php

//======================================================================================================================
// Some defines in case the main plugin is included later on

if (!defined('CUAR_PLUGIN_DIR'))
{
    define('CUAR_PLUGIN_DIR', WP_PLUGIN_DIR . '/customer-area');
    define('CUAR_INCLUDES_DIR', CUAR_PLUGIN_DIR . '/src/php');
}

//======================================================================================================================
// If we are active and the main plugin is not (or not installed), output an error notice!

if (!function_exists('cuar_is_main_plugin_missing'))
{
    /**
     * Show a message to warn that the main plugin is either not installed or not activated
     */
    function cuar_add_missing_plugin_notice()
    {
        echo '<div class="error"><p>';
        echo '<strong>Error: </strong>WP Customer Area add-ons are active but the main plugin is not installed!';
        echo '</p></div>';
    }

    /**
     * @return bool true if the main plugin is either not installed or not activated
     */
    function cuar_is_main_plugin_missing()
    {
        $is_missing = !file_exists(CUAR_PLUGIN_DIR . '/customer-area.php');
        if ($is_missing)
        {
            add_action('admin_notices', 'cuar_add_missing_plugin_notice');
        }
        return $is_missing;
    }
}

//======================================================================================================================
// Check PHP version

if (!function_exists('cuar_check_requirements'))
{
    function cuar_check_requirements()
    {
        $errors = [];

        if (PHP_MAJOR_VERSION === 4 || (PHP_MAJOR_VERSION === 5 && PHP_MINOR_VERSION < 6))
        {
            $errors[] = 'WP Customer Area requires PHP 5.6 or 7.x';
        }

        if (version_compare((float)get_bloginfo('version'), '4.7', '<'))
        {
            $errors[] = 'WP Customer Area requires WordPress 4.7';
        }

        return $errors;
    }

    function cuar_self_deactivate()
    {
        deactivate_plugins(plugin_basename(CUAR_PLUGIN_DIR . '/customer-area.php'));
    }

    function cuar_requirements_admin_notice()
    {
        if (!current_user_can('activate_plugins')) return;

        $messages = '<div class="error"><p><strong>WP Customer Area</strong> cannot be activated due to the following missing requirements:</p><ul>';
        $missing_requirements = cuar_check_requirements();
        foreach ($missing_requirements as $msg)
        {
            $messages .= "<li>$msg</li>";
        }
        $messages .= '</ul></div>';
        echo $messages;

        if (isset($_GET['activate']))
        {
            unset($_GET['activate']);
        }
    }

    if (!empty(cuar_check_requirements()))
    {
        add_action('admin_init', 'cuar_self_deactivate');
        add_action('admin_notices', 'cuar_requirements_admin_notice');
    }
}


