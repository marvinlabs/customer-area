<?php /** Template version: 1.0.0 */ ?>

<div class="cuar-private-file-meta">
	<h4><?php echo apply_filters( 'cuar_private_files_after_content_title', __( 'More information about this file', 'cuar' ) ); ?></h4>
	
	<p class="private-content-information"><?php 	
		$date = sprintf("<em>%s</em>", get_the_date() );
		$author = sprintf("<em>%s</em>", get_the_author_meta( 'display_name' ) );
		$recipients = sprintf("<em>%s</em>", cuar_get_the_owner() );
	
		printf( __( 'File created on %1$s by %2$s for %3$s', 'cuar' ), $date, $author, $recipients  ); ?></p>
	
	<table class="cuar-private-file-list">
	  <tbody>
			<tr class="cuar-private-file">
				<td class="title">
					<?php cuar_the_file_name( get_the_ID() ); ?>
				</td>
				<td class="links download-link">
					<a href="<?php cuar_the_file_link( get_the_ID(), 'download' ); ?>" title="<?php esc_attr_e( 'Download', 'cuar' ); ?>">
						<?php _e( 'Download', 'cuar' ); ?></a>
				</td>
			</tr>
		</tbody>
	</table>
</div>