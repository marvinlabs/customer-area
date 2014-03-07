<?php /** Template version: 1.0.0 */ ?>

<h3><?php 
	$current_user = $this->get_current_user();	
	printf( __('Hello %s,', 'cuar'), $current_user->display_name );
?></h3>

<p><?php _e('Please find below your account details', 'cuar' ); ?></p>

<?php $this->print_account_fields(); ?>