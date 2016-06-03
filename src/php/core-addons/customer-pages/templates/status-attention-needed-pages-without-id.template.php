<?php
/** Template version: 2.0.0
 *
 * -= 2.0.0 =-
 * - Add cuar- prefix to bootstrap classes
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

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

<div class="cuar-needs-attention">
	<h3><?php _e( 'Missing front-office pages', 'cuar' ); ?></h3>
	
	<p>
		<?php _e( 'Some pages of the customer area have not yet been created. Those pages are responsible for displaying the private content you have assigned to your users.', 'cuar' ); ?>
		<?php printf( __( 'You can review which pages are currently created on the <a href="%1$s">pages settings page</a>.', 'cuar' ), 
					admin_url( 'admin.php?page=wpca-settings&tab=cuar_customer_pages' ) ); ?>
	</p>
		 
	<p class="cuar-suggested-action"><span class="text"><?php _e('Suggested action', 'cuar' ); ?></span>
		<input type="submit" id="cuar-create-all-missing-pages" name="cuar-create-all-missing-pages" value="<?php esc_attr_e( 'Create missing pages', 'cuar' ); ?> &raquo;" class="button button-primary" />
		<?php wp_nonce_field( 'cuar-create-all-missing-pages', 'cuar-create-all-missing-pages_nonce' ); ?>
		
		<script type="text/javascript">
		<!--
			jQuery(document).ready(function($) {
				$('#cuar-create-all-missing-pages').click('click', function(){
					var answer = confirm( "<?php echo str_replace( '"', '\\"', __('Are you sure that you want to create all the pages that are currently missing (this operation cannot be undone)?', 'cuar') ); ?>" );
					return answer;
				});
			});
		//-->
		</script>
	</p>	
</div>