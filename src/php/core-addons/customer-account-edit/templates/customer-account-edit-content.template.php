<?php /** Template version: 1.1.0
 *
 * -= 1.1.0 =-
 * - Added addresses
 *
 * -= 1.0.0 =-
 * - Initial version
 */
?>
		
<h3><?php 
	$current_user = get_userdata( get_current_user_id() );	
	printf( __('Hello %s,', 'cuar'), $current_user->display_name );
?></h3>

<p><?php _e('You can update your account details. If you leave the password fields empty, your password will not be modified.', 'cuar' ); ?></p>


<?php $this->print_form_header(); ?>

<?php $this->print_account_fields(); ?>

<?php $this->print_address_fields(); ?>

<?php $this->print_submit_button( __( 'Submit', 'cuar' ) ); ?>
<?php $this->print_form_footer(); ?>
	