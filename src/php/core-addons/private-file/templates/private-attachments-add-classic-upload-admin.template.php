<?php /**
 * Template version: 3.0.0
 * Template zone: admin
 */ ?>

<?php /** @var int $post_id */ ?>

<div class="cuar-classic-uploader cuar-js-classic-uploader" data-post-id="<?php echo esc_attr($post_id); ?>">
    <?php wp_nonce_field('cuar-attach-classic-upload-' . $post_id, 'cuar_classic-upload_' . $post_id); ?>

    <div class="metabox-row">
        <div class="cuar-dropzone cuar-js-dropzone" id="cuar_dropzone" data-post-id="<?php echo esc_attr($post_id); ?>">
            <p class="cuar-dropzone-message">
                <span class="fa fa-upload"></span>
                <br/>
                <?php _e('Drop your files here or click me!', 'cuar'); ?>
            </p>
            <input type="file" name="cuar_file" class="cuar-file-input cuar-js-file-input"/>
        </div>
    </div>
</div>

<script type="text/javascript">
    <!--
    jQuery(document).ready(function ($) {
        $('.cuar-js-classic-uploader').classicUploader();
    });
    //-->
</script>