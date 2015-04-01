<?php /** Template version: 1.0.0 */ ?>

<?php
/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
?>

<?php
require_once(CUAR_INCLUDES_DIR . '/core-addons/log/log-table.class.php');
$logsTable = new CUAR_LogTable();
$logsTable->prepare_items();
?>

<div class="wrap cuar-plugin-logs">
    <h2><?php _e('Logs', 'cuar'); ?></h2>

    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="cuar-do-logs-action" value="1"/>
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>

        <?php
        $start_date = isset($_POST['start-date']) ? sanitize_text_field($_POST['start-date']) : '';
        $end_date = isset($_POST['end-date']) ? sanitize_text_field($_POST['end-date']) : '';
        ?>

        <div class="cuar-list-table-filter">
            <label for="start-date"><?php _e('Between', 'cuar'); ?> </label>
            <input type="text" id="start-date" name="start-date" class="cuar_datepicker"
                   value="<?php echo esc_attr($start_date); ?>" placeholder="dd/mm/yyyy"/>

            <label for="end-date"> <?php _e('and', 'cuar'); ?> </label>
            <input type="text" id="start-date" name="end-date" class="cuar_datepicker"
                   value="<?php echo esc_attr($end_date); ?>" placeholder="dd/mm/yyyy"/>

            <input type="submit" name="filter_action" id="post-query-submit" class="button"
                   value="<?php esc_attr_e('Apply', 'cuar'); ?>">
        </div>

        <!-- Now we can render the completed list table -->
        <?php $logsTable->display() ?>

    </form>
</div>