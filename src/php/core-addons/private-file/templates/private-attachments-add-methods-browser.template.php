<?php /** Template version: 1.0.0

 */
?>

<?php /** @var int $post_id */ ?>
<?php /** @var array $select_methods */ ?>

<div class="cuar-file-attachment-manager">
    <span class="label">
        <label for="cuar_file_selector_input"><?php _e('How do you want to add the files?', 'cuar'); ?></label>
    </span>
    <select id="cuar_file_selector_input" name="cuar_file_selector_input" class="cuar-file-selector-input">
        <?php foreach ($select_methods as $method_id => $method) : ?>
            <option value="<?php echo esc_attr($method_id); ?>"><?php echo $method['label']; ?>&nbsp;&nbsp;</option>
        <?php endforeach; ?>
    </select>

    <div class="cuar-file-selectors">
        <?php foreach ($select_methods as $method_id => $method) : ?>
            <div class="cuar-file-selector" style="display: none;" data-method="<?php echo esc_attr($method_id); ?>">
                <p class="cuar-hint"><?php echo $method['caption']; ?></p>
                <?php do_action('cuar/private-content/files/render-select-method?id=' . $method_id, $post_id); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cuar-file-attachment-errors"></div>
</div>


<script type="text/javascript">
    <!--
    jQuery(document).ready(function ($) {
        $('.cuar-file-attachment-manager').fileAttachmentManager();
    });
    //-->
</script>