<?php if ( $pages_query->have_posts() ) : ?>

<h3><?php echo apply_filters( 'cuar_private_pages_section_title', __( 'Latest Pages', 'cuar' ) ); ?></h3>

<?php 
	$item_template = $this->plugin->get_template_file_path(
			CUAR_INCLUDES_DIR . '/core-addons/private-page',
			"list_private_pages-item.template.php",
			'templates'); 
?>	
	
<table class="cuar-private-page-list cuar-item-list"><tbody>
<?php 	while ( $pages_query->have_posts() ) : $pages_query->the_post(); global $post; ?>
	<tr class="cuar-private-page"><?php	include( $item_template ); ?></tr>		
<?php 	endwhile; ?>
</tbody></table>

<?php else : ?>

<?php do_action( 'cuar_dashboard_no_latest_pages' ); ?>

<?php endif; ?>

<?php wp_reset_postdata(); ?>
