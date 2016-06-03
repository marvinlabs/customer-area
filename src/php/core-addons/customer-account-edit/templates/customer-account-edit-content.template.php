<?php /**
 * Template version: 3.0.0
 * Template zone: frontend
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.1.0 =-
 * - Added addresses
 *
 * -= 1.0.0 =-
 * - Initial version
 */

$current_user = get_userdata(get_current_user_id());
?>

<?php $this->print_form_header(); ?>

    <div class="page-heading">
        <div class="media mb30 clearfix">
            <div class="media-left pr30">
                <?php echo get_avatar($current_user->ID); ?>
            </div>
            <div class="media-body va-m" style="width: 100%;">

                <div class="cuar-title media-heading text-primary"><?php echo $current_user->display_name; ?>
                    <small> - Profile</small>
                </div>

                <?php $this->print_submit_button(__('Submit', 'cuar')); ?>
            </div>
        </div>

        <ul class="nav panel-tabs-border panel-tabs panel-tabs-left pl20 pr20">
            <li class="active mn">
                <a href="#cuar-js-account-edit-fields" data-toggle="tab" aria-expanded="true">
                    <?php _e("General", "cuar"); ?>
                </a>
            </li>
            <li class="mn">
                <a href="#cuar-js-account-edit-address-fields" data-toggle="tab" aria-expanded="false">
                    <?php _e("Addresses", "cuar"); ?>
                </a>
            </li>
        </ul>

    </div>
    <div class="tab-content pn br-n">
        <div id="cuar-js-account-edit-fields" class="tab-pane active">
            <?php $this->print_account_fields(); ?>
        </div>
        <div id="cuar-js-account-edit-address-fields" class="tab-pane">
            <div class="row">
                <?php $this->print_address_fields(); ?>
            </div>
        </div>
    </div>

<?php $this->print_form_footer(); ?>