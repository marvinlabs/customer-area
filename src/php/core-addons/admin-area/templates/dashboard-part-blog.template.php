<?php /** Template version: 1.0.0 */ ?>

<?php
include_once(ABSPATH . WPINC . '/feed.php');

$max_blog_items = 5;
$feed_link = 'http://wp-customerarea.com' . __('/feed/rss', 'cuar');
$feed = fetch_feed($feed_link);
?>

<div class="cuar-feed-content">

<?php if (is_wp_error($feed)) : ?>
    <p><?php echo $feed->get_error_message(); ?></p>
<?php else: ?>
    <ul class="cuar-blog-posts">
<?php   $count = 0;
        $items = $feed->get_items();
        foreach ($items as $item) :
?>
         <li>
<?php       if ($enclosure = $item->get_enclosure()) : ?>
             <a href="<?php echo $item->get_permalink(); ?>" class="cuar-feed-image">
                 <img src="<?php echo $enclosure->get_link(); ?>" />
             </a>
<?php       endif; ?>
             <h3 class="cuar-feed-title"><a href="<?php echo $item->get_permalink();?>"><?php echo $item->get_title();?></a></h3>
             <span class="cuar-feed-date"><?php echo $item->get_date(get_option('date_format'));?></span>
             <div class="cuar-feed-summary"><?php echo $item->get_description();?></div>
             <div class="clear"></div>
         </li>
<?php
            $count++;
            if ($max_blog_items > 0 && $count >= $max_blog_items) break;
        endforeach; ?>
    </ul>
<?php endif; ?>

</div>