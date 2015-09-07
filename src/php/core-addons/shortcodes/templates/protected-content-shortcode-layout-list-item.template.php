<?php /** Template version: 1.0.0 */ ?>

<?php
global $post;

$is_author = get_the_author_meta('ID')==get_current_user_id();
if ( $is_author ) {
    $subtitle_popup = esc_attr(__( 'You published this content', 'cuar' ));
    $subtitle = sprintf( __( 'Published for %s', 'cuar' ), cuar_get_the_owner() );
} else {
    $subtitle_popup = esc_attr(sprintf( __( 'Published for %s', 'cuar' ), cuar_get_the_owner() ));
    $subtitle = sprintf( __( 'Published by %s on %s', 'cuar' ), get_the_author_meta( 'display_name' ), get_the_date() );
}

$title_popup = esc_attr(sprintf( __( 'Uploaded on %s', 'cuar' ), get_the_date() ));

$extra_class = ' ' . get_post_type();
$extra_class = apply_filters( 'cuar/templates/list-item/extra-class?post-type=' . get_post_type(), $extra_class, $post );
?>

<li class="cuar-item<?php echo $extra_class; ?>">
    <a href="<?php the_permalink(); ?>" title="<?php echo $title_popup; ?>" class="cuar-title"><?php the_title(); ?></a>
    <br>
    <span title="<?php echo $subtitle_popup; ?>" class="cuar-subtitle"><?php echo $subtitle; ?></span>
</li>