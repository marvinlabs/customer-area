<?php /** Template version: 1.0.0 */ ?>

<div class="wrap cuar-content-list">
    <h2><?php
        echo $post_type_object->labels->name;
        foreach ($title_links as $label => $url)
        {
            printf(' <a href="%2$s" class="add-new-h2">%1$s</a>', $label, $url);
        }
        ?></h2>
    <?php
    do_action('cuar/core/admin/content-list-page/after-title');
    do_action('cuar/core/admin/content-list-page/after-title?post_type=' . $post_type);
    ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="cuar-do-content-list-action" value="1"/>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>

        <?php
        do_action('cuar/core/admin/content-list-page/before-table');
        do_action('cuar/core/admin/content-list-page/before-table?post_type=' . $post_type);
        $listTable->display();
        do_action('cuar/core/admin/content-list-page/after-table?post_type=' . $post_type);
        do_action('cuar/core/admin/content-list-page/after-table');
        ?>
    </form>
</div>