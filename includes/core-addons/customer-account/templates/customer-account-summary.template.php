<h3><?php 
	printf( __('Hello %s,', 'cuar'), $current_user->display_name );
?></h3>

<p><?php _e('Please find below your account details', 'cuar' ); ?></p>

<?php $this->print_account_fields(); ?>