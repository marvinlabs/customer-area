<?php if ( $files_query->have_posts() ) : ?>

<h3><?php echo apply_filters( 'cuar_private_files_section_title', __( 'Latest Files', 'cuar' ) ); ?></h3>

<?php 
	$item_template = $this->plugin->get_template_file_path(
			CUAR_INCLUDES_DIR . '/core-addons/private-file',
			"list_private_files-item.template.php",
			'templates' ); 
?>	
	
<table class="cuar-private-file-list cuar-item-list"><tbody>
<?php 	while ( $files_query->have_posts() ) : $files_query->the_post(); global $post; ?>
	<tr class="cuar-private-file"><?php	include( $item_template ); ?></tr>
<?php 	endwhile; ?>
</tbody></table>

<?php else : ?>

<?php do_action( 'cuar_dashboard_no_latest_files' ); ?>

<?php endif; ?>

<?php wp_reset_postdata(); ?>
