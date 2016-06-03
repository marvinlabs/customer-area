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
$title_popup = esc_attr(sprintf( __( 'Uploaded on %s', 'cuar' ), get_the_date() ));
?>

<li>
    <a href="<?php the_permalink(); ?>" title="<?php echo $title_popup; ?>" class="cuar-title"><?php the_title(); ?></a>
</li>