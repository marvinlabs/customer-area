<?php

if ( ! function_exists('cuar_custom_editor_styles'))
{
    /**
     * Load custom styles for TinyMCE
     */
    function cuar_custom_editor_styles( $mce_css ) {
        if (is_admin()) return $mce_css;
        $mce_css .= ', ' . plugins_url( 'assets/css/custom-editor-style.css', __FILE__ );
        return $mce_css;
    }
    add_action( 'mce_css', 'cuar_custom_editor_styles' );
}

// Includes functions from base theme
include(CUAR_PLUGIN_DIR . '/skins/frontend/master/cuar-functions.php');