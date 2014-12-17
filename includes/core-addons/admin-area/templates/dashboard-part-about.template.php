<?php /** Template version: 1.0.0 */ ?>

<div class="cuar-installer-section cuar-about-section cuar-whatsnew-section">
    <h2 class="cuar-section-title"><?php _e("What's new in this version", 'cuar'); ?></h2>
    <?php $this->print_whats_new(); ?>
</div>

<div class="clear"></div>

<div class="cuar-installer-section cuar-about-section cuar-fromwebsite-section">
    <h2 class="cuar-section-title"><?php _e("From our blog", "cuar"); ?></h2>
    <?php $this->print_latest_blog_posts(); ?>
</div>

