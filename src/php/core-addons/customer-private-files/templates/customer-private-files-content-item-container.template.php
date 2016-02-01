<?php
/** Template version: 3.0.0
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
global $post;

$is_author = get_the_author_meta('ID') == get_current_user_id();
if ($is_author)
{
    $subtitle_popup = __('You uploaded this file', 'cuar');
    $subtitle = sprintf(__('Published for %s', 'cuar'), cuar_get_the_owner());
}
else
{
    $subtitle_popup = sprintf(__('Published for %s', 'cuar'), cuar_get_the_owner());
    $subtitle = sprintf(__('Published by %s', 'cuar'), get_the_author_meta('display_name'));
}
$title_popup = sprintf(__('Uploaded on %s', 'cuar'), get_the_date());

$file_count = cuar_get_the_attached_file_count($post->ID);
?>

<tr>
    <td class="cuar-title">
        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($title_popup); ?>"><?php the_title(); ?></a>
    </td>
    <td class="cuar-owner">
        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($subtitle_popup); ?>"><?php echo $subtitle; ?></a>
    </td>
    <td class="cuar-file-count">
        <span class="label label-rounded label-default"><?php echo sprintf(_n('%1$s file', '%1$s files', $file_count, 'cuar'), $file_count); ?></span>
    </td>
</tr>
