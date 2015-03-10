<?php /** Template version: 1.0.0 */ ?>

<div class="wrap cuar-dashboard-screen">

    <div class="cuar-hero">
        <h1><?php echo $page_title; ?></h1>

        <div class="cuar-messages">
            <p class="cuar-primary-message"><?php echo $hero_message_primary; ?></p>
            <p class="cuar-secondary-message"><?php echo $hero_message_secondary; ?></p>
        </div>

        <div class="cuar-badge">
            <img src="<?php echo $this->plugin->get_admin_theme_url(); ?>/assets/img/logo-badge.png" />
            <p><?php printf( __( 'version %s', 'cuar' ), $this->plugin->get_version() ); ?></p>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="cuar-content">
        <?php include($content_template); ?>
    </div>

</div>