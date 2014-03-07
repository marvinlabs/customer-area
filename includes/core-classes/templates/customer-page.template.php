<?php /** Template version: 1.0.0 */ ?>

<?php
	$page_classes = array( 'cuar-page-' . $this->page_description['slug'] ); 
	if ( $this->has_page_sidebar() ) $page_classes[] = "cuar-page-with-sidebar";
	else $page_classes[] = "cuar-page-without-sidebar";
?>
<div class="cuar-page <?php echo implode( ' ', $page_classes ); ?>">
	<div class="cuar-page-header"><?php 
		$this->print_page_header( $args, $shortcode_content ); 
	?></div>
	
	<div class="cuar-page-content"><?php 
		$this->print_page_content( $args, $shortcode_content ); 
	?></div>
	
	<?php if ( $this->has_page_sidebar() ) : ?>	
	<div class="cuar-page-sidebar"><?php 
		$this->print_page_sidebar( $args, $shortcode_content ); 
	?></div>	
	<?php endif; ?>
	
	<div class="cuar-page-footer"><?php 
		$this->print_page_footer( $args, $shortcode_content ); 
	?></div>
</div>