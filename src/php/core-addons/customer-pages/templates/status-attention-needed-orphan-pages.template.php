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
	<h3><?php _e( 'Orphan pages', 'cuar' ); ?></h3>
	
	<p>
		<?php _e( 'Some pages in your site seem to contain Customer Area shortcodes but are not registered in the Customer Area pages settings. Those are probably pages you had created and ' 
				. 'trashed when originally set up the plugin. You should delete them for good either manually or by letting us do it for you.', 'cuar' ); ?>
	</p>
		 
	<p class="cuar-suggested-action"><span class="text"><?php _e('Suggested action', 'cuar' ); ?></span>
		<input type="submit" id="cuar-remove-orphan-pages" name="cuar-remove-orphan-pages" value="<?php esc_attr_e( 'Delete orphan pages', 'cuar' ); ?> &raquo;" class="button button-primary" />
		<?php wp_nonce_field( 'cuar-remove-orphan-pages', 'cuar-remove-orphan-pages_nonce' ); ?>
		
		<script type="text/javascript">
		<!--
			jQuery(document).ready(function($) {
				$('#cuar-remove-orphan-pages').click('click', function(){
					var answer = confirm( "<?php echo str_replace( '"', '\\"', __('Are you sure that you want to delete those pages (this operation cannot be undone)?', 'cuar') ); ?>" );
					return answer;
				});
			});
		//-->
		</script>
	</p>	
</div>