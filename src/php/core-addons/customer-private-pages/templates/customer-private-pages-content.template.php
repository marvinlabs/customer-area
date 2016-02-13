<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Initial version
 *
 */ ?>

<div class="cuar-collection cuar_private_page">
    <div class="row clearfix mb-md">
        <div class="col-xs-7">
            <!-- CURRENTLY NOT IMPLEMENTED
            <div class="mix-controls ib">
                <form id="cuar-js-collection-filters" class="controls">
                    <div class="btn-group ib mr10">
                        <button type="button" class="btn btn-default hidden-xs">
                            <span class="fa fa-folder"></span>
                        </button>
                        <div class="btn-group">
                            <fieldset>
                                <select class="cuar-js-collection-filters-buttons">
                                    <option value=""><?php _e('All categories', 'cuar'); ?></option>
                                    <option value=".category1">category1</option>
                                    <option value=".category2">Scategory2</option>
                                    <option value=".category3">category3</option>
                                </select>
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
            -->
        </div>
        <div class="col-xs-5 text-right">
            <div class="btn-group">
                <button type="button" id="cuar-js-collection-to-grid" class="btn btn-primary">
                    <span class="fa fa-th"></span>
                </button>
                <button type="button" id="cuar-js-collection-to-list" class="btn btn-default">
                    <span class="fa fa-navicon"></span>
                </button>
            </div>
        </div>
    </div>

    <div id="cuar-js-collection-gallery" class="cuar-collection-content">
        <div class="fail-message alert alert-warning">
            <?php _e('No items were found matching the selected filters', 'cuar'); ?>
        </div>
        <?php
        while ($content_query->have_posts()) {
            $content_query->the_post();
            global $post;

            include($item_template);
        }
        ?>
        <div class="gap"></div>
        <div class="gap"></div>
        <div class="gap"></div>
        <div class="gap"></div>
    </div>
</div>