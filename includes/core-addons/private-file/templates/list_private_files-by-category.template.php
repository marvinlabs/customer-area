<div class="accordion-container">	
<?php 
	$item_template = $this->plugin->get_template_file_path(
			CUAR_INCLUDES_DIR . '/core-addons/private-file',
			"list_private_files-item-{$display_mode}.template.php",
			'templates',
			"list_private_files-item.template.php" );
	
	if ( !empty( $file_categories ) ) : 
		foreach ( $file_categories as $category ) : 
			$this->print_category_files($category, $item_template, $current_user_id, ' &raquo; ' );
		endforeach; 
	else : 	
		include( $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-addons/private-file',
				'list_private_files-empty.template.php',
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