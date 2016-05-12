<?php /**
 * Template version: 3.0.0
 * Template zone: frontend
 */ ?>

<?php /** @var int $post_id */ ?>
<?php /** @var string $ftp_dir */ ?>
<?php /** @var array $ftp_files */ ?>

<div class="cuar-ftp-uploader cuar-js-ftp-uploader" data-post-id="<?php echo esc_attr($post_id); ?>">
    <?php wp_nonce_field('cuar-attach-ftp-folder-' . $post_id, 'cuar_ftp-folder_' . $post_id); ?>

    <?php if ( !file_exists($ftp_dir)) : ?>
        <div class="alert alert-danger"><?php _e("The FTP upload folder does not exist.", 'cuar'); ?></div>
    <?php elseif ($this->is_dir_empty($ftp_dir)) : ?>
        <div class="alert alert-default"><?php _e("The FTP upload folder is empty.", 'cuar'); ?></div>
    <?php else : ?>
        <div class="form-group">
            <label for="cuar_ftp_file_selection"><?php _e('Pick a file', 'cuar'); ?></label>
            <select id="cuar_ftp_file_selection" name="cuar_ftp_file_selection" multiple="multiple" class='form-control cuar-js-ftp-file-selection'>
                <?php foreach ($ftp_files as $filename) : $file_path = $ftp_dir . '/' . $filename; ?>
                    <option value="<?php echo esc_attr($filename); ?>"><?php echo esc_html($filename); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <div class="checkbox-custom">
                <input name="cuar_copy_file" type="checkbox" id="cuar_copy_file" checked="checked" value="open" class="cuar-js-copy-file-checkbox">
                <label for="cuar_ftp_copy_file"><?php _e('Copy the file (do not delete it from the FTP folder)', 'cuar'); ?></label>
            </div>
        </div>

        <div class="form-group">
            <a href="#" class="btn btn-default cuar-js-ftp-add-files button"><?php _e('Attach selected file(s)', 'cuar'); ?></a>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    <!--
    (function ($) {
        "use strict";
        $(document).ready(function ($) {
            $('#cuar-js-content-container').on('cuar:wizard:initialized', function(){
                $('.cuar-js-ftp-uploader').ftpUploader();

                $("#cuar_ftp_file_selection").select2({
                    dropdownParent: $('#cuar_ftp_file_selection').parent(),
                    width: "100%",
                    allowClear: true,
                    minimumResultsForSearch: 8
                });
            });
        });
    })(jQuery);
    //-->
</script>