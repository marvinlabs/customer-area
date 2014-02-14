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

<p><?php _e('Pressing the button below will reset all your Customer Area settings to the default values. This cannot be undone!', 'cuar' ); ?></p>

<p>
	<?php wp_nonce_field( 'cuar-reset-all-settings', 'cuar-reset-all-settings_nonce' ); ?>
	<input type="submit" name="cuar-reset-all-settings" id="cuar-reset-all-settings" class="button button-primary cuar-reset-all-settings" value="<?php esc_attr_e( 'Reset default settings', 'cuar' ); ?>" />
</p>
<script type="text/javascript">
<!--
	jQuery(document).ready(function($) {
		$('input.cuar-reset-all-settings').click('click', function(){
			var answer = confirm( "<?php echo str_replace( '"', '\\"', __('Are you sure that you want to reset all the settings to their default values (this operation cannot be undone)?', 'cuar' ) ); ?>" );
			return answer;
		});
	});
//-->
</script>