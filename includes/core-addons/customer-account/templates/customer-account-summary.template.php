<h3><?php 
	$current_user = get_userdata( get_current_user_id() );
	printf( __('Hello %s,', 'cuar'), $current_user->display_name );
?></h3>

<?php do_action( 'cuar_customer_account_summary' ); ?>