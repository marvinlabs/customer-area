<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.2.0 =-
 * - Added addresses
 *
 * -= 1.1.0 =-
 * - Added updated message
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */

$current_user = $this->get_current_user();
?>

<div class="page-heading">
    <div class="media clearfix">
        <div class="media-left pr30">
            <?php echo get_avatar($current_user->ID); ?>
        </div>
        <div class="media-body va-m" style="width: 100%;">

            <h2 class="media-heading text-primary"><?php echo $current_user->display_name; ?>
                <small> - Profile</small>
            </h2>

            <?php
            if (isset($_GET['updated']) && $_GET['updated'] == 1) {
                printf('<p class="alert alert-info mt15 mbn">%s</p>', __('Your profile has been updated', 'cuar'));
            }
            ?>

        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-heading"><span class="panel-title"><?php _e('General', 'cuar'); ?></span></div>
    <div class="panel-body">
        <h3><?php _e('Account Details', 'cuar'); ?></h3>
        <?php $this->print_account_fields(); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-heading"><span class="panel-title"><?php _e('Addresses', 'cuar'); ?></span></div>
    <div class="panel-body">
        <?php $this->print_address_fields(); ?>
    </div>
</div>