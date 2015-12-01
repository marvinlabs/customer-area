<?php /** Template version: 1.0.0 */ ?>

<?php
include_once(ABSPATH . WPINC . '/feed.php');

$feeds = array(
    array(
        'title'     => __('Save money with our bundles', 'cuar'),
        'url'       => 'http://wp-customerarea.com' . __('/downloads/category/wpca-bundles/feed/rss', 'cuar')
    ),
    array(
        'title'     => __('Add-ons to enhance your private area', 'cuar'),
        'url'       => 'http://wp-customerarea.com' . __('/downloads/category/wpca-add-ons/feed/rss', 'cuar')
    ),
    array(
        'title'     => __('Themes optimised for WP Customer Area', 'cuar'),
        'url'       => 'http://wp-customerarea.com' . __('/downloads/category/wpca-themes/feed/rss', 'cuar')
    ),
);

foreach ($feeds as $f) :
    $feed = fetch_feed($f['url']);
?>
<h2><?php echo $f['title']; ?></h2>
<div class="cuar-feed-content">
<?php if (is_wp_error($feed)) : ?>
    <p><?php echo $feed->get_error_message(); ?></p>
<?php else: ?>
    <p><a href="<?php echo 'http://wp-customerarea.com' . __('/add-ons-and-themes', 'cuar'); ?>" class="button"><?php _e('Show all', 'cuar'); ?></a></p>
    <div class="cuar-add-ons">
<?php
        $odd = true;
        $items = $feed->get_items();
        foreach ($items as $item) :
            $odd = !$odd;
?>
        <div class="cuar-addon <?php echo $odd ? 'cuar-odd' : 'cuar-even'; ?>">
<?php       if ($enclosure = $item->get_enclosure()) : ?>
             <a href="<?php echo $item->get_permalink(); ?>" class="cuar-addon-image">
                 <img src="<?php echo $enclosure->get_link(); ?>" />
             </a>
<?php       endif; ?>
            <div class="meta">
                <h3 class="cuar-addon-title"><a href="<?php echo $item->get_permalink();?>"><?php echo $item->get_title();?></a></h3>
                <p class="cuar-addon-description"><?php echo $item->get_description();?></p>
            </div>
         </div>
<?php       if ($odd) echo '<p class="forcelinebreak"></p>'; ?>
<?php   endforeach; ?>
    </div>
    <div class="clear"></div>
<?php endif; ?>

</div>

<?php endforeach; ?>