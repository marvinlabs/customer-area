<?php /** Template version: 1.0.0

 */

/** @var int $post_id */
?>

<div class="cuar-classic-uploader" data-post-id="<?php echo esc_attr($post_id); ?>">
    <?php wp_nonce_field('cuar-attach-classic-upload-' . $post_id, 'cuar_classic-upload_' . $post_id); ?>

    <div class="metabox-row">
        <div class="cuar-dropzone" id="cuar_dropzone" data-post-id="<?php echo esc_attr($post_id); ?>">
            <p class="cuar-dropzone-message">
                <span class="dashicons dashicons-upload"></span>
                <br/>
                <?php _e('Drop your files here or click me!', 'cuar'); ?>
            </p>
            <input type="file" name="cuar_file" class="cuar-file-input"/>
        </div>
    </div>
</div>

<script type="text/javascript">
    <!--
    jQuery(document).ready(function ($) {
        $('.cuar-classic-uploader').classicUploader();
    });
    //-->
</script>