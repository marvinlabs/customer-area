<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Initial version
 *
 */ ?>

<div class="cuar-content-block cuar-private-pages">
    <div class="panel-body">
        <div class="cuar-private-pages-list cuar-item-list">
            <table class="table">
                <thead>
                <tr class="">
                    <th><?php _e('Title', 'cuar'); ?></th>
                    <th><?php _e('Owners', 'cuar'); ?></th>
                </tr>
                </thead>

                <tbody>
                <?php
                while ($content_query->have_posts()) {
                    $content_query->the_post();
                    global $post;

                    include($item_template);
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>