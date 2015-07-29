<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.3.0 =-
 * - Compatibility with the new multiple attached files
 * - New hooks for attachment items
 *
 * -= 1.2.0 =-
 * - Updated to new responsive markup
 *
 * -= 1.1.0 =-
 * - Added file size
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

<?php
global $post;
$extra_class = ' ' . get_post_type();
$extra_class = apply_filters('cuar/templates/single-post/footer/extra-class?post-type=' . get_post_type(), $extra_class, $post);

$date = sprintf("<em>%s</em>", get_the_date());
$author = sprintf("<em>%s</em>", get_the_author_meta('display_name'));
$recipients = sprintf("<em>%s</em>", cuar_get_the_owner());

/** @var array $attached_files */
$attached_files = cuar_get_the_attached_files($post->ID);
?>

<div class="cuar-single-post-footer cuar-file<?php echo $extra_class; ?>">

    <div class="cuar-meta-category cuar-content-meta">
        <div class="cuar-row">
            <div class="cuar-meta-block cuar-author">
                <h4><span class="cuar-dashicons cuar-dashicons-businessman"></span> <?php _e('Author', 'cuar'); ?></h4>

                <p><?php echo $author; ?></p>
            </div>

            <div class="cuar-meta-block cuar-owner">
                <h4><span class="cuar-dashicons cuar-dashicons-groups"></span> <?php _e('Recipient', 'cuar'); ?></h4>

                <p><?php echo $recipients; ?></p>
            </div>

            <div class="cuar-meta-block cuar-date">
                <h4><span class="cuar-dashicons cuar-dashicons-calendar"></span> <?php _e('Date', 'cuar'); ?></h4>

                <p><?php echo $date; ?></p>
            </div>
        </div>
    </div>

    <div class="cuar-meta-category cuar-file-meta">
        <div class="cuar-row">
            <div class="cuar-meta-block cuar-file">
                <h4><span class="cuar-dashicons cuar-dashicons-admin-links"></span> <?php _e('Attached files', 'cuar'); ?>
                </h4>
                <ul class="cuar-attached-files">
                    <?php foreach ($attached_files as $file_id => $file) : ?>
                        <li>
                            <a href="<?php cuar_the_attached_file_link($post->ID, $file); ?>" title="<?php esc_attr_e('Get file', 'cuar'); ?>">
                                <?php do_action('cuar/templates/file-attachment-item/before-caption', $post->ID, $file); ?>
                                <span class="cuar-file-caption"><?php cuar_the_attached_file_caption($post->ID, $file); ?></span>
                                <?php do_action('cuar/templates/file-attachment-item/after-caption', $post->ID, $file); ?>
                                <span class="cuar-file-size">(<?php cuar_the_attached_file_size($post->ID, $file); ?>)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>