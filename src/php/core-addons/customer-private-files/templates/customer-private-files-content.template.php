<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.1.0 =-
 * - Updated markup
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<div class="cuar-content-block cuar-private-files">
    <div class="cuar-private-file-list cuar-item-list cuar-gallery-page">

        <div class="mh15 pv15 br-b br-light">
            <div class="row">
                <div class="col-xs-7">
                    <div class="mix-controls ib">
                        <form class="controls" id="select-filters">
                            <!-- We can add an unlimited number of "filter groups" using the following format: -->
                            <div class="btn-group ib mr10">
                                <button type="button" class="btn btn-default hidden-xs">
                                    <span class="fa fa-folder"></span>
                                </button>
                                <div class="btn-group">
                                    <fieldset>
                                        <select id="filter1">
                                            <option value="">All Folders</option>
                                            <option value=".folder1">Publicity</option>
                                            <option value=".folder2">Spain Vacation</option>
                                            <option value=".folder3">Sony Demo</option>
                                        </select>
                                    </fieldset>
                                </div>
                            </div>
                            <div class="btn-group ib mr10">
                                <button type="button" class="btn btn-default hidden-xs">
                                    <span class="fa fa-tag"></span>
                                </button>
                                <div class="btn-group">
                                    <fieldset>
                                        <select id="filter2">
                                            <option value="">All Labels</option>
                                            <option value=".label1">Work</option>
                                            <option value=".label3">Clients</option>
                                            <option value=".label2">Family</option>
                                        </select>
                                    </fieldset>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
                <div class="col-xs-5 text-right">
                    <button type="button" id="mix-reset" class="btn btn-default mr5">Clear Filters</button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default to-grid">
                            <span class="fa fa-th"></span>
                        </button>
                        <button type="button" class="btn btn-default to-list">
                            <span class="fa fa-navicon"></span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mix-controls hidden">
                <form class="controls admin-form" id="checkbox-filters">
                    <!-- We can add an unlimited number of "filter groups" using the following format: -->

                    <fieldset class="">
                        <h4>Cars</h4>

                        <label class="option block mt10">
                            <input type="checkbox" value=".circle">
                            <span class="checkbox"></span>Circle
                        </label>

                    </fieldset>

                    <button id="mix-reset2">Clear All</button>
                </form>

            </div>
        </div>

        <div id="mix-container">

            <div class="fail-message">
                <span>No items were found matching the selected filters</span>
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
</div>