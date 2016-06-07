<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<?php /** @var string $layout */ ?>
<?php /** @var string $type */ ?>
<?php /** @var string $before_items_template */ ?>
<?php /** @var string $item_template */ ?>
<?php /** @var string $after_items_template */ ?>
<?php /** @var WP_Query $query */ ?>

<div class="cuar-css-wrapper cuar-sc-protected-content cuar-sc-protected-content-<?php echo $layout ?> <?php echo $type ?>">
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