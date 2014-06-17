<?php /** Template version: 1.2.0

-= 1.2.0 =-
- Updated to new responsive markup

-= 1.1.0 =-
- Added file size
 
-= 1.0.0 =-
- Initial version

*/
?>

<?php 
	global $post;	
	$extra_class = ' ' . get_post_type();
	$extra_class = apply_filters( 'cuar/templates/single-post/footer/extra-class?post-type=' . get_post_type(), $extra_class, $post );
	
	$date = sprintf("<em>%s</em>", get_the_date() );
	$author = sprintf("<em>%s</em>", get_the_author_meta( 'display_name' ) );
	$recipients = sprintf("<em>%s</em>", cuar_get_the_owner() );
?>

<div class="cuar-single-post-footer cuar-file<?php echo $extra_class; ?>">
	
	<div class="meta-category content-meta">
		<div class="row">
			<div class="meta-block author">
				<h4><span class="dashicons dashicons-businessman"></span> <?php _e( 'Author', 'cuar' ); ?></h4>
				<p><?php echo $author; ?></p>
			</div>
			
			<div class="meta-block owner">
				<h4><span class="dashicons dashicons-groups"></span> <?php _e( 'Recipient', 'cuar' ); ?></h4>
				<p><?php echo $recipients; ?></p>
			</div>

			<div class="meta-block date">
				<h4><span class="dashicons dashicons-calendar"></span> <?php _e( 'Date', 'cuar' ); ?></h4>
				<p><?php echo $date; ?></p>
			</div>
		</div>
	</div>
	
	<div class="meta-category file-meta">
		<div class="row">
			<div class="meta-block file">
				<h4><span class="dashicons dashicons-admin-links"></span> <?php _e( 'Attachment', 'cuar' ); ?></h4>
				<p><a href="<?php cuar_the_file_link( get_the_ID(), 'download' ); ?>" title="<?php esc_attr_e( 'Download', 'cuar' ); ?>">
					<?php cuar_the_file_name( get_the_ID() ); ?> (<?php cuar_the_file_size( get_the_ID() ); ?>)
				</a></p>
			</div>
		</div>
	</div>
</div>