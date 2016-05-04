<?php /**
 * Template version: 1.0.0
 * Template zone: frontend
 */ ?>

<?php /** @var int $post_id */ ?>
<?php /** @var array $select_methods */ ?>

<div class="cuar-js-file-attachment-manager">
    <div class="well well-lg clearfix">
        <div class="row">
            <div class="col-md-8">
                <label for="cuar_file_selector_input"><?php _e('How do you want to add the files?', 'cuar'); ?></label>
            </div>
            <div class="col-md-4">
                <select id="cuar_file_selector_input" name="cuar_file_selector_input" class="form-control pull-right cuar-js-file-selector-input">
                    <?php foreach ($select_methods as $method_id => $method) : ?>
                        <option value="<?php echo esc_attr($method_id); ?>"><?php echo $method['label']; ?>&nbsp;&nbsp;</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="cuar-js-file-selectors">
        <?php foreach ($select_methods as $method_id => $method) : ?>
            <div class="cuar-js-file-selector" style="display: none;" data-method="<?php echo esc_attr($method_id); ?>">
                <div class="alert alert-info pastel">
                    <i class="fa fa-info pr10"></i>
                    <?php echo $method['caption']; ?>
                </div>

                <?php do_action('cuar/private-content/files/render-select-method?id=' . $method_id, $post_id); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cuar-js-file-attachment-errors">
    </div>
    
    <div class="cuar-js-error-template" style="display: none;">
        <div class="alert alert-danger alert-dismissable cuar-js-error">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="fa fa-remove pr10"></i></button>
            <span class="cuar-js-message"></span>
        </div>
    </div>
</div>
