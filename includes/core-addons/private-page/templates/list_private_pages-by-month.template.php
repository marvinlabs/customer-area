<?php if ( $pages_query->have_posts() ) : ?>

<?php 
	$item_template = $this->plugin->get_template_file_path(
			CUAR_INCLUDES_DIR . '/core-addons/private-page',
			"list_private_pages-item.template.php",
			'templates'); 
	
	$current_month = '';
?>

<div class="accordion-container">		
	
<?php 	while ( $pages_query->have_posts() ) : $pages_query->the_post(); global $post; ?>

<?php $the_date = get_the_date( 'F Y' ); ?>

<?php 		if ( empty( $current_month ) ) : 
				$current_month = $the_date; ?>
				
<h4 title="<?php printf( __( 'Clic to show the pages published in %s', 'cuar' ), $current_month );?>" class="cuar-private-page-section cuar-section">
	<?php echo $current_month; ?>
</h4>
<div class="accordion-section-content"><table class="cuar-private-page-list cuar-item-list"><tbody>
	
<?php 		elseif ( $current_month!=$the_date ) : 
				$current_month = $the_date; ?>
				
</tbody></table></div>
<h4 title="<?php printf( __( 'Clic to show the pages published in %s', 'cuar' ), $current_month );?>" class="cuar-private-page-section cuar-section">
	<?php echo $current_month; ?>
</h4>
<div class="accordion-section-content"><table class="cuar-private-page-list"><tbody>
<?php 		endif; ?>
	<?php	include( $item_template ); ?>		
<?php 	endwhile; ?>
</tbody></table></div>

</div>

<script type="text/javascript">
<!--
jQuery(document).ready(function($) {
	$( "div.accordion-container" ).accordion({
			heightStyle: "content",
			header: "h4",
			animate: 250
		});
});
//-->
</script>

<?php else : ?>
<?php 	include( $this->plugin->get_template_file_path(
					CUAR_INCLUDES_DIR . '/core-addons/private-page',
					'list_private_pages-empty.template.php',
					'templates' ));	?>
<?php endif; ?>

<?php wp_reset_postdata(); ?>
