<?php 
if ( !dynamic_sidebar( 'cuar_customer_files_sidebard' ) ) {
	$default_widget_args = array( 
			'before_widget' 	=> '<aside id="%1$s" class="widget %2$s">',
			'after_widget' 		=> "</aside>",
			'before_title' 		=> '<h3 class="widget-title">',
			'after_title' 		=> '</h3>',
		);
	
	$w = new CUAR_PrivateFileCategoriesWidget();
	$w->widget( $default_widget_args, array( 
			'title'	=> __( 'Categories', 'cuar' ),
		) );
	
	$w = new CUAR_PrivateFileDatesWidget();
	$w->widget( $default_widget_args, array( 
			'title'	=> __( 'Archives', 'cuar' ),
		) );
}