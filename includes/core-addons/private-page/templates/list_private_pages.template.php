<div class="cuar-private-page-container">
<?php if ( $pages_query->have_posts() ) : ?>

<?php 
	$item_template = $this->plugin->get_template_file_path(
			CUAR_INCLUDES_DIR . '/core-addons/private-page',
			"list_private_pages-item.template.php",
			'templates'); 
?>	
	
<table class="cuar-private-page-list"><tbody>
<?php 	while ( $pages_query->have_posts() ) : $pages_query->the_post(); global $post; ?>
	<?php	include( $item_template ); ?>
<?php 	endwhile; ?>
</tbody></table>

<?php else : ?>
<?php 	include( $this->plugin->get_template_file_path(
					CUAR_INCLUDES_DIR . '/core-addons/private-page',
					'list_private_pages-empty.template.php',
					'templates' ));	?>
<?php endif; ?>

<?php wp_reset_postdata(); ?>

</div>