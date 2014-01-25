<h2 class="cuar_page_title"><?php _e( 'Hello', 'cuar'); ?></h2>
<p><?php _e( 'You must login to access your own customer area. '
	. 'If you do not have an account yet, please register or ' 
	. 'contact us so that we can create it.', 'cuar' ); ?></p>

<ul>
	<li><a href="<?php echo wp_login_url( $redirect_to_url ); ?>"><?php _e( 'Login', 'cuar' ); ?></a></li>
<?php if ( get_option( 'users_can_register' ) ) : ?>
	<li><a href="<?php echo wp_registration_url(); ?>"><?php _e( 'Register', 'cuar' ); ?></a></li>
<?php endif; ?>
</ul>
