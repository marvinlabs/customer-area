<?php
if ( !function_exists('cuar_load_theme_scripts'))
{

    /**
     * TODO: Librairies shouldn't always be enabled.
     * Load theme particular scripts
     */
    function cuar_load_skin_scripts()
    {
        if (cuar_is_customer_area_page(get_queried_object_id()) || cuar_is_customer_area_private_content(get_the_ID()))
        {
            $cuar_plugin = cuar();

            $cuar_plugin->enable_library('bootstrap.affix');
            $cuar_plugin->enable_library('bootstrap.alert');
            $cuar_plugin->enable_library('bootstrap.button');
            $cuar_plugin->enable_library('bootstrap.carousel');
            $cuar_plugin->enable_library('bootstrap.collapse');
            $cuar_plugin->enable_library('bootstrap.dropdown');
            $cuar_plugin->enable_library('bootstrap.modal');
            $cuar_plugin->enable_library('bootstrap.popover');
            $cuar_plugin->enable_library('bootstrap.scrollspy');
            $cuar_plugin->enable_library('bootstrap.tab');
            $cuar_plugin->enable_library('bootstrap.tooltip');
            $cuar_plugin->enable_library('bootstrap.transition');

            $cuar_plugin->enable_library('jquery.mixitup');

            wp_register_script('customer-area-utilities',
                CUAR_PLUGIN_URL . '/assets/frontend/js/customer-area.min.js',
                array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable'),
                $cuar_plugin->get_version(),
                true);

            wp_register_script('customer-area-master-skin',
                CUAR_PLUGIN_URL . '/skins/frontend/master/assets/js/main.js',
                array('jquery', 'customer-area-utilities'),
                $cuar_plugin->get_version(),
                true);

            wp_register_style('customer-area-master-eqcss',
                CUAR_PLUGIN_URL . '/skins/frontend/master/assets/css/eqcss/queries.eqcss',
                array(),
                $cuar_plugin->get_version(),
                true);

            wp_enqueue_style('customer-area-master-eqcss');
            wp_enqueue_script('customer-area-master-skin');
        }
    }

    add_action('wp_enqueue_scripts', 'cuar_load_skin_scripts');
}


if ( !function_exists('cuar_enable_bootstrap_nav_walker'))
{

    /**
     * Use the bootstrap navwalker for our navigation menu to output bootstrap-friendly HTML.
     * @param $args
     * @return
     */
    function cuar_enable_bootstrap_nav_walker($args)
    {
        require_once(CUAR_PLUGIN_DIR . '/src/php/helpers/bootstrap-nav-walker.class.php');
        $new_args = $args;

        $new_args['depth'] = 2;
        $new_args['container'] = 'div';
        $new_args['container_class'] = 'nav-container collapse navbar-collapse';
        $new_args['menu_class'] = 'nav navbar-nav';
        $new_args['fallback_cb'] = 'CUAR_BootstrapNavWalker::fallback';
        $new_args['walker'] = new CUAR_BootstrapNavWalker();

        return $new_args;
    }

    add_filter('cuar/core/page/nav-menu-args', 'cuar_enable_bootstrap_nav_walker');
}

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
