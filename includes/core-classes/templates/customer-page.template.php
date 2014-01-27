<?php
	$page_classes = array( 'cuar-page-' . $this->page_description['slug'] ); 
	if ( $this->has_page_sidebar() ) $page_classes[] = "cuar-page-with-sidebar";
	else $page_classes[] = "cuar-page-without-sidebar";
	
	$page_slug = $this->get_slug();
?>
<div class="cuar-page <?php echo implode( ' ', $page_classes ); ?>">
	<div class="cuar-page-header"><?php 
		do_action( 'cuar_before_page_header-' . $page_slug );
		
		$this->print_page_header( $args, $shortcode_content ); 
		
		do_action( 'cuar_after_page_header-' . $page_slug );
	?></div>
	
	<div class="cuar-page-content"><?php 
		do_action( 'cuar_before_page_content-' . $page_slug );
		
		$this->print_page_content( $args, $shortcode_content ); 
		
		do_action( 'cuar_after_page_content-' . $page_slug );
	?></div>
	
	<?php if ( $this->has_page_sidebar() ) : ?>	
	<div class="cuar-page-sidebar"><?php 
		do_action( 'cuar_before_page_sidebar-' . $page_slug );
		
		$this->print_page_sidebar( $args, $shortcode_content ); 
		
		do_action( 'cuar_after_page_sidebar-' . $page_slug );
	?></div>	
	<?php endif; ?>
	
	<div class="cuar-page-footer"><?php 
		do_action( 'cuar_before_page_footer-' . $page_slug );
		
		$this->print_page_footer( $args, $shortcode_content ); 
		
		do_action( 'cuar_after_page_footer-' . $page_slug );
	?></div>
</div>