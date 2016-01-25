<?php /** Template version: 1.0.0
 *
 * -=1.0.1=-
 * - change heading for WP 4.4
 *
 */ ?>

<div class="wrap cuar-logs-list-page cuar-list-table-page">
    <h1><?php _e('Logs', 'cuar'); ?></h1>

    <h2 class="screen-reader-text"><?php _e('Filter Log Event List', 'cuar'); ?></h2>

    <form method="GET" action="<?php echo admin_url('admin.php'); ?>">
        <input type="hidden" name="cuar-do-logs-action" value="1"/>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>

        <?php
        $start_date = isset($_POST['start-date']) ? sanitize_text_field($_POST['start-date']) : '';
        $end_date = isset($_POST['end-date']) ? sanitize_text_field($_POST['end-date']) : '';
        $logger = CUAR_Plugin::get_instance()->get_logger();
        $type_filter = isset($_POST['event-type']) ? $_POST['event-type'] : 0;
        ?>

        <?php $logs_table->views() ?>
        <br/>
        <div class="cuar-list-table-filter">
            <a class="cuar-filter-toggle"><?php _e('Toggle advanced filters', 'cuar'); ?></a>

            <?php $collapse_panel = $logs_table->is_search_active() ? '' : 'display: none;'; ?>
            <div class="cuar-filter-panel" style="<?php echo $collapse_panel; ?>">
                <div class="cuar-filter-row">
                    <label for="event-type"><?php _e('Event type', 'cuar'); ?> </label>
                    <select name="event-type" id="cat" class="postform">
                        <option value="0"><?php _e('Any type', 'cuar'); ?></option>
                        <?php
                        $types = $logger->get_valid_event_types(true);
                        foreach ($types as $slug => $label)
                        {
                            $selected = selected($type_filter, $slug, false);
                            echo sprintf('<option value="%1$s" %3$s>%2$s</option>', $slug, $label, $selected);
                        }
                        ?>
                    </select>

                    <input type="submit" name="filter_action" id="post-query-submit" class="button cuar-filter-button"
                           value="<?php esc_attr_e('Filter events', 'cuar'); ?>">
                </div>

                <div class="cuar-filter-row">
                    <label for="start-date"><?php _e('Between', 'cuar'); ?> </label>
                    <input type="text" id="start-date" name="start-date" class="cuar_datepicker"
                           value="<?php echo esc_attr($start_date); ?>" placeholder="dd/mm/yyyy"/>

                    <label for="end-date"> <?php _e('and', 'cuar'); ?> </label>
                    <input type="text" id="end-date" name="end-date" class="cuar_datepicker"
                           value="<?php echo esc_attr($end_date); ?>" placeholder="dd/mm/yyyy"/>
                </div>
            </div>
        </div>

        <!-- Now we can render the completed list table -->
        <?php $logs_table->display() ?>

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