<?php
if (!function_exists('cuar_load_theme_scripts')) {

    /**
     * TODO: Librairies shouldn't always be enabled.
     * Load theme particular scripts
     */
    function cuar_load_skin_scripts()
    {
        if (cuar_is_customer_area_page(get_queried_object_id()) || cuar_is_customer_area_private_content(get_the_ID())) {
            $cuar_plugin = cuar();

            $cuar_plugin->enable_library('bootstrap.affix');
            $cuar_plugin->enable_library('bootstrap.alert');
            $cuar_plugin->enable_library('bootstrap.button');
            //$cuar_plugin->enable_library('bootstrap.carousel');
            $cuar_plugin->enable_library('bootstrap.collapse');
            $cuar_plugin->enable_library('bootstrap.dropdown');
            //$cuar_plugin->enable_library('bootstrap.modal');
            //$cuar_plugin->enable_library('bootstrap.popover');
            //$cuar_plugin->enable_library('bootstrap.scrollspy');
            //$cuar_plugin->enable_library('bootstrap.tab');
            $cuar_plugin->enable_library('bootstrap.tooltip');
            //$cuar_plugin->enable_library('bootstrap.transition');

            $cuar_plugin->enable_library('jquery.cookie');

            $cuar_plugin->enable_library('jquery.mixitup');

            wp_register_script('customer-area-utilities',
                CUAR_PLUGIN_URL . '/assets/frontend/js/customer-area.min.js',
                array('jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable'),
                $cuar_plugin->get_version(),
                true);

            wp_register_script('customer-area-master-skin',
                CUAR_PLUGIN_URL . '/skins/frontend/master/assets/js/main.min.js',
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

if (!function_exists('cuar_images_sizes')) {
    /**
     * Generate images sizes for wpca
     */
    function cuar_images_sizes()
    {
        add_theme_support('post-thumbnails');
        add_image_size('wpca-thumb', 220, 150, true);
        add_image_size('wpca-banner', 800, 220, true);
    }

    add_action('after_setup_theme', 'cuar_images_sizes');
}


if (!function_exists('cuar_enable_bootstrap_nav_walker')) {

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

if (!function_exists('cuar_custom_editor_styles')) {
    /**
     * Load custom styles for TinyMCE
     * @param $mce_css
     * @return string
     */
    function cuar_custom_editor_styles($mce_css)
    {
        if (is_admin()) return $mce_css;

        if (cuar_is_customer_area_page(get_queried_object_id()) || cuar_is_customer_area_private_content(get_the_ID())) {
            $mce_css .= ', ' . plugins_url('assets/css/custom-editor-style.css', __FILE__);
        }
        return $mce_css;
    }

    add_action('mce_css', 'cuar_custom_editor_styles');
}

if (!function_exists('cuar_custom_excerpt_length')) {
    /**
     * Custom excerpt length
     * @param $length
     * @return int
     */
    function cuar_custom_excerpt_length($length)
    {
        if (cuar_is_customer_area_page(get_queried_object_id()) || cuar_is_customer_area_private_content(get_the_ID())) {
            return 20;
        } else {
            return $length;
        }
    }

    add_filter('cuar_excerpt_length', 'cuar_custom_excerpt_length', 999);
}

if (!function_exists('cuar_custom_excerpt_more')) {
    /**
     * Remove more link to excerpt
     * @param $more
     * @return string
     */
    function cuar_custom_excerpt_more($more)
    {
        if (cuar_is_customer_area_page(get_queried_object_id()) || cuar_is_customer_area_private_content(get_the_ID())) {
            return '';
        } else {
            return $more;
        }
    }

    add_filter('cuar_excerpt_more', 'cuar_custom_excerpt_more');
}

if (!function_exists('cuar_trim_excerpt')) {
    /**
     * Generates an excerpt from the content, if needed.
     *
     * The excerpt word amount will be 55 words and if the amount is greater than
     * that, then the string ' [&hellip;]' will be appended to the excerpt. If the string
     * is less than 55 words, then the content will be returned as is.
     *
     * The 55 word limit can be modified by plugins/themes using the excerpt_length filter
     * The ' [&hellip;]' string can be modified by plugins/themes using the excerpt_more filter
     *
     * @since 1.5.0
     *
     * @param string $text Optional. The excerpt. If set to empty, an excerpt is generated.
     * @return string The excerpt.
     */
    function cuar_trim_excerpt($text = '')
    {
        $raw_excerpt = $text;
        if ('' == $text) {
            $text = get_the_content('');

            $text = strip_shortcodes($text);

            /** This filter is documented in wp-includes/post-template.php */
            // We do not want this
            // $text = apply_filters('the_content', $text);
            $text = str_replace(']]>', ']]&gt;', $text);

            /**
             * Filter the number of words in an excerpt.
             *
             * @since 2.7.0
             *
             * @param int $number The number of words. Default 55.
             */
            $excerpt_length = apply_filters('cuar_excerpt_length', 55);
            /**
             * Filter the string in the "more" link displayed after a trimmed excerpt.
             *
             * @since 2.9.0
             *
             * @param string $more_string The string shown within the more link.
             */
            $excerpt_more = apply_filters('cuar_excerpt_more', ' ' . '[&hellip;]');
            $text = wp_trim_words($text, $excerpt_length, $excerpt_more);
        }
        /**
         * Filter the trimmed excerpt string.
         *
         * @since 2.8.0
         *
         * @param string $text The trimmed text.
         * @param string $raw_excerpt The text prior to trimming.
         */
        return apply_filters('cuar_trim_excerpt', $text, $raw_excerpt);
    }
}

if (!function_exists('cuar_remove_auto_excerpt')) {
    /**
     * Prevent the excerpt to be generated from the_content
     */
    function cuar_remove_auto_excerpt()
    {
        // if (cuar_is_customer_area_page(get_queried_object_id()) || cuar_is_customer_area_private_content(get_the_ID())) {
            remove_filter('get_the_excerpt', 'wp_trim_excerpt');
            add_filter('get_the_excerpt', 'cuar_trim_excerpt');
        // }
    }

    add_action('after_setup_theme', 'cuar_remove_auto_excerpt');
}
