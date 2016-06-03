<?php

//======================================================================================================================
// Some defines in case the main plugin is included later on

if ( !defined('CUAR_PLUGIN_DIR'))
{
    define('CUAR_PLUGIN_DIR', WP_PLUGIN_DIR . '/customer-area');
    define('CUAR_INCLUDES_DIR', CUAR_PLUGIN_DIR . '/src/php');
}

//======================================================================================================================
// If we are active and the main plugin is not (or not installed), output an error notice!

if ( !function_exists('cuar_is_main_plugin_missing'))
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
        if ( $is_missing)
        {
            add_action('admin_notices', 'cuar_add_missing_plugin_notice');
        }
        return $is_missing;
    }
}