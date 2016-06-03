<?php /** Template version: 1.1.0

 -= 1.1.0 =-
 * Add uninstall button

 */ ?>

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

<h3><?php _e( 'Export settings', 'cuar' ); ?></h3>

<p><?php _e('You can export all the settings to a JSON file. You can then use that file to restore your settings using the import settings field below.', 'cuar' ); ?></p>

<p>
	<?php wp_nonce_field( 'cuar-export-settings', 'cuar-export-settings_nonce' ); ?>
	<input type="submit" name="cuar-export-settings" id="cuar-export-settings" class="button cuar-export-settings" value="<?php esc_attr_e( 'Export settings', 'cuar' ); ?>" />
</p>

<h3><?php _e( 'Import settings', 'cuar' ); ?></h3>

<p><?php _e('You can import all the settings you had previously exported from a JSON file.', 'cuar' ); ?></p>

<p>
	<?php wp_nonce_field( 'cuar-import-settings', 'cuar-import-settings_nonce' ); ?>
	<input type="file" name="cuar-settings-file" id="cuar-settings-file" /><br>
	<input type="submit" name="cuar-import-settings" id="cuar-import-settings" class="button cuar-import-settings" value="<?php esc_attr_e( 'Import settings', 'cuar' ); ?>" />
</p>


<h3><?php _e( 'Reset settings', 'cuar' ); ?></h3>

<p><?php _e('Pressing the button below will reset all your Customer Area settings to the default values. This cannot be undone!', 'cuar' ); ?></p>

<p>
	<?php wp_nonce_field( 'cuar-reset-all-settings', 'cuar-reset-all-settings_nonce' ); ?>
	<input type="submit" name="cuar-reset-all-settings" id="cuar-reset-all-settings" class="button cuar-reset-all-settings" value="<?php esc_attr_e( 'Reset default settings', 'cuar' ); ?>" />
</p>

<h3><?php _e( 'Uninstall WP Customer Area', 'cuar' ); ?></h3>

<p><?php _e('Pressing the button below will clean-up any data related to WP Customer Area (posts, taxonomies, etc.). This cannot be undone!', 'cuar' ); ?></p>

<p>
	<?php wp_nonce_field( 'cuar-uninstall', 'cuar-uninstall_nonce' ); ?>
	<input type="submit" name="cuar-uninstall" id="cuar-uninstall" class="button cuar-uninstall" value="<?php esc_attr_e( 'Clean-up database', 'cuar' ); ?>" />
</p>
<script type="text/javascript">
<!--
	jQuery(document).ready(function($) {
		$('input.cuar-reset-all-settings').click('click', function(){
			var answer = confirm( "<?php echo str_replace( '"', '\\"', __('Are you sure that you want to reset all the settings to their default values (this operation cannot be undone)?', 'cuar' ) ); ?>" );
			return answer;
		});

		$('input.cuar-uninstall').click('click', function(){
			var answer = confirm( "<?php echo str_replace( '"', '\\"', __('Are you sure that you want to delete everything related to WP Customer Area (this operation cannot be undone)?', 'cuar' ) ); ?>" );
			return answer;
		});
	});
//-->
</script>