<div class="accordion-container">	
<?php 
	$item_template = $this->plugin->get_template_file_path(
			CUAR_INCLUDES_DIR . '/core-addons/private-page',
			"list_private_pages-item-{$display_mode}.template.php",
			'templates',
			"list_private_pages-item.template.php" );
	
	if ( !empty( $page_categories ) ) : 
		foreach ( $page_categories as $category ) : 
			$this->print_category_pages($category, $item_template, $current_user_id, ' &raquo; ' );
		endforeach; 
	else : 	
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/private-page',
				'list_private_pages-empty.template.php',
				'templates' ));	
	endif; 
?>
	
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

<?php wp_reset_postdata(); ?>

</div>