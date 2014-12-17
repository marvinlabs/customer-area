<?php /** Template version: 1.0.0 */ ?>

<div class="wrap cuar-dashboard-screen">

    <div class="cuar-hero">
        <h1><?php echo $page_title; ?></h1>

        <div class="cuar-messages">
            <p class="cuar-primary-message"><?php echo $hero_message_primary; ?></p>
        </div>

        <div class="cuar-badge">
            <img src="<?php echo $this->plugin->get_admin_theme_url(); ?>/images/logo-badge.png" />
            <p><?php printf( __( 'version %s', 'cuar' ), $this->plugin->get_version() ); ?></p>
        </div>
    </div>

    <div class="clear"></div>

    <ul class="cuar-related-actions">
        <li>
            <a href="<?php echo admin_url('admin.php?page=cuar-settings'); ?>" class="button button-primary"><?php _e('Settings', 'cuar'); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url(__('http://wp-customerarea.com/documentation/', 'cuar')); ?>" class="button button-primary" target="_blank"><?php _e('Documentation', 'cuar'); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url(__('http://wp-customerarea.com/support/', 'cuar')); ?>" class="button button-primary" target="_blank"><?php _e('Support', 'cuar'); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url(__('http://wp-customerarea.com/add-ons-and-themes/', 'cuar')); ?>" class="button button-primary" target="_blank"><?php _e('Add-ons and themes', 'cuar'); ?></a>
        </li>
        <li>
            <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://wp-customerarea.com/" data-text="<?php echo esc_url('An open-source (free) plugin for #WordPress to share private content, easily.', 'cuar'); ?>" data-via="WPCustomerArea" data-size="large" data-hashtags="WPCA">Tweet</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        </li>
        <li class="clear"></li>
    </ul>

    <div class="clear"></div>

    <h2 class="nav-tab-wrapper cuar-dashboard-tabs">
    <?php foreach ($tabs as $tab => $tab_desc) :
            $classes = 'nav-tab';
            if ($current_tab==$tab) $classes .= ' nav-tab-active';
    ?>
        <a class="<?php echo $classes; ?>" href="<?php echo esc_url($tab_desc['url']); ?>"><?php echo $tab_desc['label']; ?></a>
    <?php endforeach; ?>
    </h2>

    <div class="clear"></div>

    <div class="cuar-content">
        <?php include($tab_content_template); ?>
    </div>

</div>