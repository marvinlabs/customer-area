<?php /**
 * Template version: 3.0.0
 * Template zone: admin
 */ ?>

<?php /** @var int $post_id */ ?>
<?php /** @var string $ftp_dir */ ?>
<?php /** @var array $ftp_files */ ?>

<div class="cuar-ftp-uploader cuar-js-ftp-uploader" data-post-id="<?php echo esc_attr($post_id); ?>">
    <?php wp_nonce_field('cuar-attach-ftp-folder-' . $post_id, 'cuar_ftp-folder_' . $post_id); ?>

    <div class="metabox-row">
        <?php if ( !file_exists($ftp_dir)) : ?>
            <p><?php _e("The FTP upload folder does not exist.", 'cuar'); ?></p>
        <?php elseif ($this->is_dir_empty($ftp_dir)) : ?>
            <p><?php _e("The FTP upload folder is empty.", 'cuar'); ?></p>
        <?php else : ?>
            <span class="label"><label for="cuar_ftp_file_selection"><?php _e('Pick a file', 'cuar'); ?></label></span>
            <span class="field">
                <select id="cuar_ftp_file_selection" name="cuar_ftp_file_selection" multiple="multiple" class='cuar-js-ftp-file-selection'>
                    <?php foreach ($ftp_files as $filename) : $file_path = $ftp_dir . '/' . $filename; ?>
                        <option value="<?php echo esc_attr($filename); ?>"><?php echo esc_html($filename); ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="#" class="cuar-js-ftp-add-files button"><?php _e('Attach selected file(s)', 'cuar'); ?></a>
            </span>
        <?php endif; ?>
    </div>

    <div class="metabox-row">
        <label for="cuar_ftp_copy_file" class="selectit">
            <input name="cuar_copy_file" type="checkbox" id="cuar_copy_file" checked="checked" value="open" class="cuar-js-copy-file-checkbox">
            <?php _e('Copy the file (do not delete it from the FTP folder)', 'cuar'); ?>
        </label>
    </div>
</div>

<script type="text/javascript">
    <!--
    // TODO Get all this in a proper JS file
    jQuery(document).ready(function ($) {
        $('.cuar-js-ftp-uploader').ftpUploader();
    });
    //-->
</script>