<?php /**
 * Template version: 3.0.0
 * Template zone: admin
 */ ?>

<?php /** @var int $post_id */ ?>
<?php /** @var array $select_methods */ ?>

<div class="cuar-file-attachment-manager cuar-js-file-attachment-manager">
    <div class="alert alert-info clearfix">
        <label for="cuar_file_selector_input"><?php _e('How do you want to add the files?', 'cuar'); ?></label>
        <select id="cuar_file_selector_input" name="cuar_file_selector_input" class="cuar-file-selector-input cuar-js-file-selector-input pull-right">
            <?php foreach ($select_methods as $method_id => $method) : ?>
                <option value="<?php echo esc_attr($method_id); ?>"><?php echo $method['label']; ?>&nbsp;&nbsp;</option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="cuar-file-selectors cuar-js-file-selectors">
        <?php foreach ($select_methods as $method_id => $method) : ?>
            <div class="cuar-file-selector cuar-js-file-selector" style="display: none;" data-method="<?php echo esc_attr($method_id); ?>">
                <p class="cuar-hint"><?php echo $method['caption']; ?></p>
                <?php do_action('cuar/private-content/files/render-select-method?id=' . $method_id, $post_id); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cuar-file-attachment-errors cuar-js-file-attachment-errors"></div>

    <div class="cuar-js-error-template" style="display: none;">
        <p class="cuar-error cuar-js-error">
            <span class="cuar-js-message"></span>
            <a href="#" class="cuar-dismiss cuar-js-dismiss"><span class="dashicons dashicons-dismiss"></span></a>
        </p>
    </div>
</div>