<?php /** Template version: 1.0.0

 */

/** @var string $ftp_dir */
/** @var array $ftp_files */
?>

<input type="hidden" name="ftp_operation" value="<?php echo $ftp_operation; ?>" />

<div class="metabox-row">
    <span class="label"><label for="cuar_selected_ftp_file"><?php _e('Pick a file', 'cuar'); ?></label></span>
    <span class="field">
<?php if ( !file_exists($ftp_dir)) : ?>
    <?php _e("The FTP upload folder does not exist.", 'cuar'); ?>
<?php elseif ($this->is_dir_empty($ftp_dir)) : ?>
    <?php _e("The FTP upload folder is empty.", 'cuar'); ?>
<?php else : ?>
    <select id="cuar_selected_ftp_file" name="cuar_selected_ftp_file">
        <option value=""><?php _e('Select a file', 'cuar'); ?></option>
        <?php foreach ($ftp_files as $filename) : $filepath = $ftp_dir . '/' . $filename; ?>
            <?php if (is_file($filepath)) : ?>
                <option value="<?php echo esc_attr($filename); ?>"><?php echo esc_html($filename); ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
<?php endif; ?>
</div>