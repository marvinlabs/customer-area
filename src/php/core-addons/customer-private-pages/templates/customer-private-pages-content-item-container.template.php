<?php
/** Template version: 3.1.0
 *
 * -= 3.1.0 =-
 * - Add hooks
 *
 * -= 3.0.0 =-
 * - Improve UI for new master-skin
 *
 * -= 2.0.0 =-
 * - Add cuar- prefix to bootstrap classes
 *
 * -= 1.1.0 =-
 * - Updated markup
 * - Normalized the extra class filter name
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<?php $title_popup = sprintf(__('Uploaded on %s', 'cuar'), get_the_date()); ?>

<tr>
    <td class="cuar-title">
        <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr($title_popup); ?>"><?php the_title(); ?></a>
    </td>
    <td class="text-right cuar-extra-info">
        <?php do_action('cuar/templates/block/item/extra-info'); ?>
    </td>
</tr>