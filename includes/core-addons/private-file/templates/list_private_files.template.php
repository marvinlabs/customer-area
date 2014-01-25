<?php if ( $files_query->have_posts() ) : ?>

<?php 
	$item_template = $this->plugin->get_template_file_path(
			CUAR_INCLUDES_DIR . '/core-addons/private-file',
			"list_private_files-item-{$display_mode}.template.php",
			'templates',
			"list_private_files-item.template.php" ); 
?>	
	
<table class="cuar-private-file-list cuar-item-list"><tbody>
<?php 	while ( $files_query->have_posts() ) : $files_query->the_post(); global $post; ?>
	<?php	include( $item_template ); ?>
<?php 	endwhile; ?>
</tbody></table>

<?php else : ?>
<?php 	include( $this->plugin->get_template_file_path(
					CUAR_INCLUDES_DIR . '/core-addons/private-file',
					'list_private_files-empty.template.php',
					'templates' ));	?>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
