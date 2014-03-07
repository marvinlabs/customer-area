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

	$plugin_data = get_plugin_data( WP_CONTENT_DIR . '/plugins/' . CUAR_PLUGIN_FILE, false, false );
	$current_plugin_version = $plugin_data[ 'Version' ];
?>

<table class="widefat cuar-status-table">
	<thead>
		<tr>
			<th><?php _e( 'ID', 'cuar' ); ?></th>
			<th><?php _e( 'Name', 'cuar' ); ?></th>
			<th><?php _e( 'Required customer area version', 'cuar' ); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$addons = $this->plugin->get_registered_addons();
	uasort( $addons, 'cuar_sort_addons_by_name_callback' );
	
	foreach ( $addons as $id => $addon ) : 
		$required_version = $addon->min_cuar_version;
		$tr_class = version_compare( $required_version, $current_plugin_version, '>' ) ? 'cuar-needs-attention' : '';
?>
		<tr class="<?php echo $tr_class; ?>">
			<td><?php echo $addon->get_id(); ?></td>
			<td><?php echo $addon->get_addon_name(); ?></td>
			<td><?php echo $addon->min_cuar_version; ?></td>
		</tr>
<?php 
	endforeach; ?>
	</tbody>
</table>