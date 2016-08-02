<?php /**
 * Template version: 3.0.0
 * Template zone: frontend
 */ ?>

<?php /** @var int $post_id */ ?>
<?php /** @var array $select_methods */ ?>

<div class="cuar-js-file-attachment-manager panel">
    <div class="panel-heading">
        <label for="cuar_file_selector_input" class="panel-title"><?php _e('File Upload', 'cuar'); ?></label>
    </div>
    <div class="panel-menu clearfix" data-toggle="tooltip" data-placement="top" data-original-title="<?php esc_attr_e('How do you want to add the files?', 'cuar'); ?>">
        <select id="cuar_file_selector_input" name="cuar_file_selector_input" class="form-control pull-right cuar-js-file-selector-input">
            <?php foreach ($select_methods as $method_id => $method) : ?>
                <option value="<?php echo esc_attr($method_id); ?>"><?php echo $method['label']; ?>&nbsp;&nbsp;</option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="panel-body">

        <div class="cuar-js-file-attachment-errors"></div>

        <div class="cuar-js-error-template" style="display: none;">
            <div class="alert alert-danger alert-dismissable cuar-js-error">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                    <i class="fa fa-remove pr10"></i></button>
                <span class="cuar-js-message"></span>
            </div>
        </div>

        <div class="cuar-js-file-selectors">
            <?php foreach ($select_methods as $method_id => $method) : ?>
                <div class="cuar-js-file-selector" style="display: none;" data-method="<?php echo esc_attr($method_id); ?>">

                    <?php do_action('cuar/private-content/files/render-select-method?id=' . $method_id, $post_id); ?>

                    <div class="panel-bottom-slice">
                            <i class="fa fa-info pr10"></i>
                            <?php echo $method['caption']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

</div>
