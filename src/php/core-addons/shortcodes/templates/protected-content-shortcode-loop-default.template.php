<?php
global $post;
$post_backup = $post;

include($before_items_template);

while ( $query->have_posts() ) {
    $query->the_post();
    global $post;

    include( $item_template );
}

include($after_items_template);

$post = $post_backup;