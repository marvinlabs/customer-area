<?php
if ( !function_exists('cuar_is_active_body_class'))
{
    /**
     * Add a body class if WPCA is active to get better CSS priority on our area
     * @param $classes
     * @return array
     */
    function cuar_is_active_body_class($classes)
    {
        $classes[] = 'customer-area-active';
        return $classes;
    }

    add_action('body_class', 'cuar_is_active_body_class');
}

if ( !function_exists('cuar_load_skin_scripts'))
{

    /** Always load our scripts */
    function cuar_load_skin_scripts()
    {
        $cuar_plugin = cuar();

        // BOOTSTRAP
        // --
        $cuar_plugin->enable_library('bootstrap.affix');
        $cuar_plugin->enable_library('bootstrap.alert');
        $cuar_plugin->enable_library('bootstrap.button');
        //$cuar_plugin->enable_library('bootstrap.carousel');
        $cuar_plugin->enable_library('bootstrap.collapse');
        $cuar_plugin->enable_library('bootstrap.dropdown');
        $cuar_plugin->enable_library('bootstrap.modal');
        //$cuar_plugin->enable_library('bootstrap.popover');
        $cuar_plugin->enable_library('bootstrap.scrollspy');
        $cuar_plugin->enable_library('bootstrap.tab');
        $cuar_plugin->enable_library('bootstrap.tooltip');
        //$cuar_plugin->enable_library('bootstrap.transition');

        // PAGES COLLECTIONS
        // --
        if (cuar_is_customer_area_page(get_queried_object_id()))
        {
            $cuar_plugin->enable_library('jquery.cookie');
            $cuar_plugin->enable_library('jquery.mixitup');
        }

        // PAGES FORMS
        // --
        if (cuar_is_customer_area_page(get_queried_object_id()))
        {
            $cuar_plugin->enable_library('jquery.steps');
        }

        // SINGLE POSTS HEADERS
        // --
        if (cuar_is_customer_area_private_content(get_the_ID()))
        {
            $cuar_plugin->enable_library('jquery.slick');
        }

        // SINGLE POSTS HEADERS
        // --
        $cuar_plugin->enable_library('summernote');

        // CUSTOM SCRIPTS
        // --
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
        wp_enqueue_script('customer-area-master-skin');

        // CUSTOM STYLES
        // --
        wp_register_style('customer-area-master-eqcss',
            CUAR_PLUGIN_URL . '/skins/frontend/master/assets/css/eqcss/queries.eqcss',
            array(),
            $cuar_plugin->get_version());
        wp_enqueue_style('customer-area-master-eqcss');

        // CUSTOM FONTS
        // --
        wp_register_style('customer-area-master-fontawesome',
            CUAR_PLUGIN_URL . '/skins/frontend/master/assets/css/fonts.min.css',
            array(),
            $cuar_plugin->get_version());
        wp_enqueue_style('customer-area-master-fontawesome');
    }

    /** Only load our scripts when necessary */
    function cuar_load_skin_scripts_conditional()
    {
        if (cuar_is_customer_area_page(get_queried_object_id()) || cuar_is_customer_area_private_content(get_the_ID()))
        {
            cuar_load_skin_scripts();
        }
    }

    add_action('wp_enqueue_scripts', 'cuar_load_skin_scripts_conditional');
    add_action('cuar/core/shortcode/before-process', 'cuar_load_skin_scripts');
}

if ( !function_exists('cuar_custom_sidebar_attributes'))
{
    /**
     * Example function that you can override in your theme functions to customize
     * the behaviors of the tray sidebars
     * By default, parameters are configured to make the area height fit the window viewport
     * You can customize it to fit your need, or simply remove the data-tray-height-base
     * and data-tray-height-substract that are used to calculate the height of the main content
     * and the sidebar.
     * This function is actually just an example, so the modifications have been commented out
     * to leave the default behaviors
     *
     * @param $args array
     * @return mixed
     * @see customer-area/src/php/core-classes/templates/customer-page.template.php
     */
    function cuar_custom_sidebar_attributes($args)
    {
        //$args['data-tray-height-base'] = '';
        //$args['data-tray-height-substract'] = '';
        //$args['data-tray-height-minimum'] = 350;

        return $args;
    }

    add_action('cuar/core/page/sidebar-attributes', 'cuar_custom_sidebar_attributes');
}

if ( !function_exists('cuar_images_sizes'))
{
    /**
     * Generate images sizes for wpca
     */
    function cuar_images_sizes()
    {
        add_theme_support('post-thumbnails');
        add_image_size('wpca-thumb', 220, 150, true);
    }

    add_action('after_setup_theme', 'cuar_images_sizes');
}

if ( !function_exists('cuar_enable_bootstrap_nav_walker'))
{

    /**
     * Use the bootstrap navwalker for our navigation menu to output bootstrap-friendly HTML.
     *
     * @param $args
     *
     * @return mixed
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

if ( !function_exists('cuar_custom_editor_styles'))
{
    /**
     * Load custom styles for TinyMCE
     *
     * @param $mce_css
     *
     * @return string
     */
    function cuar_custom_editor_styles($mce_css)
    {
        if (is_admin())
        {
            return $mce_css;
        }

        if (cuar_is_customer_area_page(get_queried_object_id()) || cuar_is_customer_area_private_content(get_the_ID()))
        {
            $mce_css = ', ' . plugins_url('assets/css/styles.min.css', __FILE__);
        }

        return $mce_css;
    }

    add_filter('mce_css', 'cuar_custom_editor_styles');
}

if ( !function_exists('cuar_custom_excerpt_length'))
{
    /**
     * Custom excerpt length
     *
     * @param $length
     *
     * @return int
     */
    function cuar_custom_excerpt_length($length)
    {
        if (cuar_is_customer_area_page(get_queried_object_id()) || cuar_is_customer_area_private_content(get_the_ID()))
        {
            return 30;
        }
        else
        {
            return $length;
        }
    }

    add_filter('cuar_excerpt_length', 'cuar_custom_excerpt_length', 999);
}

if ( !function_exists('cuar_custom_excerpt_more'))
{
    /**
     * Remove more link to excerpt
     *
     * @param $more
     *
     * @return string
     */
    function cuar_custom_excerpt_more($more)
    {
        if (cuar_is_customer_area_page(get_queried_object_id()) || cuar_is_customer_area_private_content(get_the_ID()))
        {
            return '';
        }
        else
        {
            return $more;
        }
    }

    add_filter('cuar_excerpt_more', 'cuar_custom_excerpt_more');
}

if ( !function_exists('cuar_trim_excerpt'))
{
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
     *
     * @return string The excerpt.
     */
    function cuar_trim_excerpt($text = '')
    {
        $raw_excerpt = $text;
        if ('' == $text)
        {
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
         * @param string $text        The trimmed text.
         * @param string $raw_excerpt The text prior to trimming.
         */
        return apply_filters('cuar_trim_excerpt', $text, $raw_excerpt);
    }
}

if ( !function_exists('cuar_remove_auto_excerpt'))
{
    /**
     * Prevent the excerpt to be generated from the_content
     */
    function cuar_remove_auto_excerpt()
    {
        remove_filter('get_the_excerpt', 'wp_trim_excerpt');
        add_filter('get_the_excerpt', 'cuar_trim_excerpt');
    }

    add_action('after_setup_theme', 'cuar_remove_auto_excerpt');
}

if ( !function_exists('cuar_acf_field_group_class'))
{
    /**
     * Customize field groups on frontend
     */
    function cuar_acf_field_group_class($options, $id)
    {
        if ( !is_admin())
        {
            $options["layout"] = 'panel';
        }

        return $options;
    }

    add_filter('acf/field_group/get_options', 'cuar_acf_field_group_class', 10, 2);
}

if ( !function_exists('cuar_form_account_panel_head'))
{
    /**
     * Wrap defaults account fields into a panel
     */
    function cuar_form_account_panel_head()
    {
        echo '<div class="panel"><div class="panel-heading">' . __('Account details', 'cuar') . '</div><div class="panel-body">';
    }

    function cuar_form_account_panel_foot()
    {
        echo '</div></div>';
    }

    add_action('cuar/core/user-profile/edit/before_field?id=user_login', 'cuar_form_account_panel_head');
    add_action('cuar/core/user-profile/edit/after_field?id=user_pass', 'cuar_form_account_panel_foot');
}

if ( !function_exists('cuar_toolbar_profile_button'))
{
    function cuar_toolbar_profile_button($groups)
    {

        $out = '';
        $current_user = wp_get_current_user();

        $out .= '<div class="btn-group">';
        $out .= '<button type="button" class="btn btn-default dropdown-toggle mn" data-toggle="dropdown" aria-expanded="false">';
        $out .= get_avatar($current_user->user_email, 17);
        //$out .= '<span class="caret ml5"></span>';
        $out .= '</button>';
        $out .= '<ul class="dropdown-menu" role="menu" style="margin-top: 1px;">';

        if (is_user_logged_in())
        {
            $addon_account = cuar_addon('customer-account');
            $addon_account_edit = cuar_addon('customer-account-edit');
            $addon_logout = cuar_addon('customer-logout');

            $out .= '<li class="dropdown-header">Hello, ' . $current_user->display_name . '</li>';
            //$out .= '<li class="divider"></li>';
            $out .= '<li><a href="' . $addon_account->get_page_url() . '">' . __('View profile', 'cuar') . '</a></li>';
            $out .= '<li><a href="' . $addon_account_edit->get_page_url() . '">' . __('Manage account', 'cuar') . '</a></li>';
            $out .= '<li><a href="' . $addon_logout->get_page_url() . '">' . __('Logout', 'cuar') . '</a></li>';

        }
        else
        {
            $addon_login = cuar_addon('customer-login');
            $addon_register = cuar_addon('customer-register');

            $out .= '<li><a href="' . $addon_register->get_page_url() . '">' . __('Register', 'cuar') . '</a></li>';
            $out .= '<li><a href="' . $addon_login->get_page_url() . '">' . __('Login', 'cuar') . '</a></li>';
        }

        $out .= '</ul>';
        $out .= '</div>';

        $groups['welcome'] = array(
            'type' => 'raw',
            'html' => $out
        );

        return $groups;
    }

    add_filter('cuar/core/page/toolbar', 'cuar_toolbar_profile_button', 10);
}

if ( !function_exists('cuar_dev_nuancier'))
{
    /**
     * Nuancier colors for development purposes
     */
    function cuar_dev_nuancier()
    {
        $file = CUAR_PLUGIN_DIR . '/skins/frontend/master/src/less/less-vars.css';
        if ($_SERVER['HTTP_HOST'] == 'local.wordpress.dev' && file_exists($file))
        {
            $file_txt = file_get_contents($file);
            $file_regex = '/(.cuar-dev-nuance-)([^\'\s\{]*)/';

            echo '<div id="cuar-dev-nuancier"><input type="checkbox" name="cuar-dev-nuancier-toggle" id="cuar-dev-nuancier-toggle"><label for="cuar-dev-nuancier-toggle"></label><div class="cuar-dev-nuancier"><div class="cuar-dev-nuancier-wrapper">'
                . "\n";

            if (preg_match_all($file_regex, $file_txt, $file_match))
            {
                foreach ($file_match[2] as $class)
                {
                    echo '<div class="cuar-dev-nuance cuar-dev-nuance-' . $class . '"></div>' . "\n";
                }
            }

            echo '</div></div></div>' . "\n";
        }
    }
}

if ( !function_exists('cuar_dev_nuancier_styles'))
{

    /**
     * Load nuancier styles
     */
    function cuar_dev_nuancier_styles()
    {
        $file = CUAR_PLUGIN_DIR . '/skins/frontend/master/src/less/less-vars.css';
        $css = CUAR_PLUGIN_DIR . '/skins/frontend/master/assets/css/less-vars.min.css';

        if ($_SERVER['HTTP_HOST'] == 'local.wordpress.dev' && file_exists($file) && file_exists($css))
        {
            wp_register_style('customer-area-master-dev-nuancier', CUAR_PLUGIN_URL . '/skins/frontend/master/assets/css/less-vars.min.css');
            wp_enqueue_style('customer-area-master-dev-nuancier');

            add_action('wp_footer', 'cuar_dev_nuancier');
        }
    }

    add_action('wp_enqueue_scripts', 'cuar_dev_nuancier_styles');
}