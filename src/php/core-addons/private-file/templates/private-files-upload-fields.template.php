<?php /** Template version: 1.0.0

 */

/** @var string $current_attachment_list_template */
/** @var string $ajax_upload_file_template */
/** @var array $select_methods */
?>

<div class="cuar-private-file-upload-fields">
    <?php include($current_attachment_list_template); ?>

    <br/>

    <span class="label"><label for="cuar_file_select_method"><?php _e('How do you want to add the files?', 'cuar'); ?></label></span>
    <select id="cuar_file_select_method" name="cuar_file_select_method">
        <?php foreach ($select_methods as $method_id => $method) : ?>
            <option value="<?php echo esc_attr($method_id); ?>"><?php echo $method['label']; ?>&nbsp;&nbsp;</option>
        <?php endforeach; ?>
    </select>

    <?php foreach ($select_methods as $method_id => $method) : ?>
        <div id="cuar-private-file-upload-select-<?php echo esc_attr($method_id); ?>" class="cuar-file-select-method" style="display: none;" >
            <h4><?php echo $method['label']; ?></h4>
            <p class="cuar-hint"><?php echo $method['caption']; ?></p>
            <?php do_action('cuar/private-content/files/render-select-method?id=' . $method_id); ?>
        </div>
    <?php endforeach; ?>
</div>


<script type="text/javascript">
    <!--
    jQuery(document).ready(function ($) {
        $('.cuar-file-select-method:first').show();

        $('#cuar_file_select_method').change(function () {
            var selection = $(this).val();
            var target = '#cuar-private-file-upload-select-' + selection;

            // Do nothing if already visible
            if ($(target).is(":visible")) return;

            // Hide previous and then show new
            if ($('.cuar-file-select-method:visible').length <= 0) {
                $(target).fadeToggle();
            } else {
                $('.cuar-file-select-method:visible').fadeToggle("fast", function () {
                    $(target).fadeToggle();
                });
            }
        });
    });
    //-->
</script>