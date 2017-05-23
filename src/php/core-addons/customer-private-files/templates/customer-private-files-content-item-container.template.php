<?php
/** Template version: 3.1.0
 *
 * -= 3.1.0 =-
 * - Add hooks
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.2.0 =-
 * - Compatibility with the new multiple attached files
 *
 * -= 1.1.0 =-
 * - Updated markup
 * - Normalized the extra class filter name
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

<?php
$title_popup = sprintf(__('Uploaded on %s', 'cuar'), get_the_date());
$file_count = cuar_get_the_attached_file_count($post->ID);
?>

<tr>
    <td class="cuar-title">
        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($title_popup); ?>"><?php the_title(); ?></a>
    </td>
    <td class="text-right cuar-extra-info">
        <?php do_action('cuar/templates/block/item/extra-info'); ?>
        <span class="label label-default cuar-file-count"><?php echo sprintf(_n('%1$s file', '%1$s files', $file_count, 'cuar'), $file_count); ?></span>
    </td>
</tr>
