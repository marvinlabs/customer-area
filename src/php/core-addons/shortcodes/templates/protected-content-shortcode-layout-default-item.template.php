<?php
/** Template version: 3.0.0
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<?php
$title_popup = sprintf(__('Uploaded on %s', 'cuar'), get_the_date());
?>

<tr>
    <td class="cuar-title">
        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($title_popup); ?>"><?php the_title(); ?></a>
    </td>
</tr>