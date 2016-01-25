<?php /** Template version: 1.0.0
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */ ?>

<?php 
	global $post;	
	
	$is_author = get_the_author_meta('ID')==get_current_user_id();
		
	if ( $is_author ) {
		$subtitle_popup = __( 'You created this content', 'cuar' );
		$subtitle = sprintf( __( 'Published for %s', 'cuar' ), cuar_get_the_owner() );
	} else {
		$subtitle_popup = sprintf( __( 'Published for %s', 'cuar' ), cuar_get_the_owner() );
		$subtitle = sprintf( __( 'Published by %s', 'cuar' ), get_the_author_meta( 'display_name' ) );
	}

	$title_popup = sprintf( __( 'Created on %s', 'cuar' ), get_the_date() );
	
	$extra_class = ' ' . get_post_type();
	$extra_class = apply_filters( 'cuar/templates/list-item/extra-class?post-type=' . get_post_type(), $extra_class, $post );
?>

<div class="cuar-container-content cuar-item cuar-item-large<?php echo $extra_class; ?>">
	<div class="panel">
		<div class="title">
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $title_popup ); ?>"><?php the_title(); ?></a>
		</div>
		
		<div class="subtitle">
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $subtitle_popup ); ?>"><?php echo $subtitle; ?></a>
		</div>
			
<?php 	if ( has_post_thumbnail( get_the_ID() ) ) : ?>
		<div class="cover">
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $title_popup ); ?>">			
				<?php the_post_thumbnail( 'medium', array( 'class'	=> "img-responsive" ) ); ?>
			</a>
		</div>
<?php 	endif; ?>
	</div>
</div>