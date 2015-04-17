<?php /** Template version: 1.0.0 */ ?>

<div class="cuar-sc-protected-content cuar-sc-protected-content-<?php echo $layout ?>">
    <?php
    global $post;
    $post_backup = $post;

    include($before_items_template);

    while ($query->have_posts())
    {
        $query->the_post();
        global $post;

        include($item_template);
    }

    include($after_items_template);

    $post = $post_backup;
    ?>
</div>