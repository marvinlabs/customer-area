<?php /**
 * Template version: 3.0.0
 * Template zone: frontend
 */ ?>

<?php /** @var int $post_id */ ?>

<div class="cuar-classic-uploader cuar-js-classic-uploader" data-post-id="<?php echo esc_attr($post_id); ?>">
    <?php wp_nonce_field('cuar-attach-classic-upload-' . $post_id, 'cuar_classic-upload_' . $post_id); ?>

    <div class="cuar-dropzone cuar-js-dropzone" id="cuar_dropzone" data-post-id="<?php echo esc_attr($post_id); ?>">
        <div class="cuar-dropzone-message">
            <span class="fa fa-upload"></span><br />
            <span><?php _e('Drop your files here or click me!', 'cuar'); ?></span>
        </div>
        <input type="file" name="cuar_file" class="cuar-file-input cuar-js-file-input"/>
    </div>
</div>

<script type="text/javascript">
    <!--
    (function ($) {
        "use strict";
        $(document).ready(function ($) {
            $('#cuar-js-content-container').on('cuar:wizard:initialized', function(){
                $('.cuar-js-classic-uploader').classicUploader();
            });
        });
    })(jQuery);
    //-->
</script>