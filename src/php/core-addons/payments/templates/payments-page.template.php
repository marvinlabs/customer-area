<?php /** Template version: 3.0.0 */ ?>

<?php /** @var CUAR_PaymentTable $payments_table */ ?>

<div class="wrap cuar-payments-list-page cuar-list-table-page">
    <h1><?php _e('Payments', 'cuar'); ?></h1>

    <h2 class="screen-reader-text"><?php _e('Filter Payment List', 'cuar'); ?></h2>

    <form method="GET" action="<?php echo admin_url('admin.php'); ?>">
        <input type="hidden" name="cuar-do-payments-action" value="1"/>
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>"/>

        <?php
        $start_date = isset($_POST['start-date']) ? sanitize_text_field($_POST['start-date']) : '';
        $end_date = isset($_POST['end-date']) ? sanitize_text_field($_POST['end-date']) : '';
        ?>

        <?php $payments_table->views() ?>
        <br/>
        <div class="cuar-list-table-filter">
            <a class="cuar-filter-toggle"><?php _e('Toggle advanced filters', 'cuar'); ?></a>

            <?php $collapse_panel = $payments_table->is_search_active() ? '' : 'display: none;'; ?>
            <div class="cuar-filter-panel" style="<?php echo $collapse_panel; ?>">
                <div class="cuar-filter-row">
                        <label for="start-date"><?php _e('Between', 'cuar'); ?> </label>
                        <input type="text" id="start-date" name="start-date" class="cuar_datepicker"
                               value="<?php echo esc_attr($start_date); ?>" placeholder="dd/mm/yyyy"/>

                        <label for="end-date"> <?php _e('and', 'cuar'); ?> </label>
                        <input type="text" id="end-date" name="end-date" class="cuar_datepicker"
                               value="<?php echo esc_attr($end_date); ?>" placeholder="dd/mm/yyyy"/>

                    <input type="submit" name="filter_action" id="post-query-submit" class="button cuar-filter-button"
                           value="<?php esc_attr_e('Filter payments', 'cuar'); ?>">
                </div>
            </div>
        </div>

        <!-- Now we can render the completed list table -->
        <?php $payments_table->display() ?>

    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        var filterBox = $(".cuar-list-table-filter");
        var filterToggle = filterBox.find('.cuar-filter-toggle');
        var filterPanel = filterBox.find('.cuar-filter-panel');

        filterToggle.click(function (e) {
            filterPanel.slideToggle();
            e.preventDefault();
        });

        $("#delete_all").click(function(event) {
           if (!confirm("<?php esc_attr_e('Are you sure?', 'cuar'); ?>")) {
              event.preventDefault();
           }
        });
    });
</script>