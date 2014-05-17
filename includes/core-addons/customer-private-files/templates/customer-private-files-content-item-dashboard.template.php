<?php /** Template version: 1.1.0 

-= 1.1.0 =-
- Updated markup
- Normalized the extra class filter name

-= 1.0.0 =-
- Initial version

*/ ?>

<?php 
	global $post;	
	
	$is_author = get_the_author_meta('ID')==get_current_user_id();
	$file_size = cuar_get_the_file_size( get_the_ID() );
		
	if ( $is_author ) {
		$subtitle_popup = __( 'You uploaded this file', 'cuar' );
		$subtitle = sprintf( __( 'Published for %s', 'cuar' ), cuar_get_the_owner() );
	} else {
		$subtitle_popup = sprintf( __( 'Published for %s', 'cuar' ), cuar_get_the_owner() );
		$subtitle = sprintf( __( 'Published by %s', 'cuar' ), get_the_author_meta( 'display_name' ) );
	}

	$title_popup = sprintf( __( 'Uploaded on %s', 'cuar' ), get_the_date() );
	
	$extra_class = ' ' . get_post_type();
	$extra_class = apply_filters( 'cuar/templates/list-item/extra-class?post-type=' . get_post_type(), $extra_class, $post );
?>

<div class="cuar-private-file cuar-item cuar-item-wide<?php echo $extra_class; ?>">
	<div class="panel">
		<div class="title">
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $title_popup ); ?>"><?php the_title(); ?></a>
		</div>
		
		<div class="subtitle">
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $subtitle_popup ); ?>"><?php echo $subtitle; ?></a>
		</div>
		
		<div class="badges">
			<a href="<?php cuar_the_file_link( get_the_ID(), 'download' ); ?>" title="<?php echo esc_attr( sprintf( __( 'Download (%1$s)', 'cuar' ), $file_size ) ); ?>">
				<span class="download-badge dashicons dashicons-download dashicon-badge small pull-right"></span>
			</a>
		</div>
	</div>
</div>