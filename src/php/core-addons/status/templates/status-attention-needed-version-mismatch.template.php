<?php /** Template version: 1.0.0 */ ?>

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

$current_plugin_version = $this->plugin->get_version();
$mismatches = $this->plugin->addon_manager()->get_version_mismatches();
?>

<div class="cuar-needs-attention">
	<h3><?php printf( __('WP Customer Area %1$s recommends the following versions for the add-ons', 'cuar'), $current_plugin_version); ?></h3>

	<br>
	<table class="widefat">
		<tr>
			<th><?php _e( 'Add-on', 'cuar' ); ?></th>
			<th><?php _e( 'Current version', 'cuar' ); ?></th>
			<th><?php _e( 'Recommended version', 'cuar' ); ?></th>
		</tr>
		<?php foreach ($mismatches as $mismatch) : ?>
		<tr>
			<td><strong><?php echo $mismatch['name']; ?></strong></td>
			<td><?php echo $mismatch['current']; ?></td>
			<td><strong><?php echo $mismatch['recommended']; ?></strong></td>
		</tr>
		<?php endforeach; ?>
	</table>

	<p>
		<br>
		<?php _e('If you ignore this warning, there may be unexpected behaviour from the plugin. You may also be missing on security patches and bug fixes.', 'cuar'); ?>
		<strong><?php _e('You should really update the add-ons.', 'cuar'); ?></strong>
	</p>

	<p class="cuar-suggested-action"><span class="text"><?php _e('Suggested action', 'cuar' ); ?></span> 
		<a href="http://wp-customerarea.com/my-account" class="button button-primary"><?php _e( 'Download updated add-ons', 'cuar' ); ?> &raquo;</a>

		<input type="submit" id="cuar-ignore-version-mismatch" name="cuar-ignore-version-mismatch" class="button" value="<?php esc_attr_e('Ignore this warning', 'cuar'); ?> &raquo;"/>
		<?php wp_nonce_field('cuar-ignore-version-mismatch', 'cuar-ignore-version-mismatch_nonce'); ?>
	</p>
</div>