<?php
/*  Copyright 2013 MarvinLabs (contact@marvinlabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
?>

<div class="wrap cuar-wizard">
	<?php screen_icon( 'icon32-settings' ); ?>
	
	<h2><?php _e('Setup your Customer Area in seconds', 'cuar'); ?></h2>
	
	<div class="cuar-main">
	
<?php do_action( 'cuar_before_setup_wizard', $this ); ?>
	
	<p><?php _e( 'Thanks for using our Customer Area plugin. It will be just a few seconds before the plugin is ready to work.', 'cuar' ); ?></p>
	
	<form method="post" action="<?php echo admin_url( 'admin.php?page=' .  CUAR_Settings::$OPTIONS_PAGE_SLUG . '&run-setup-wizard=1' ); ?>">
	
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Page Title</th>
					<td>
						<input id="cuar_page_title" name="cuar_page_title" type="text" value="<?php esc_attr_e( 'Customer Area', 'cuar' ); ?>" />
						<p class="description"><?php _e( 'You can give any title you want to the customer area', 'cuar' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		
		
	<?php submit_button( __( 'Go, set it up!', 'cuar' ) ); ?>
	</form>
	
<?php do_action( 'cuar_after_setup_wizard', $this ); ?>

	</div>
	
	<div class="cuar-side">
		<div class="dashboard-logo">
			<img src="<?php echo $this->plugin->get_admin_theme_url(); ?>/images/logo.png">
		</div>
		
	</div>
</div>

