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

<div class="cuar-needs-attention">
	<h3><?php _e( 'Outdated plugin version', 'cuar' ); ?></h3>

	<p><?php printf( __('Some add-ons require a newer version of the main plugin (you are currently running version %1$s). You can find the list of all add-ons on the <a href="%2$s">add-ons status pages</a>.', 'cuar'), 
					$current_plugin_version,
					admin_url('admin.php?page=wpca-status&tab=installed-addons')
		 		); ?></p>
		 
	<p class="cuar-suggested-action"><span class="text"><?php _e('Suggested action', 'cuar' ); ?></span> 
		<a href="<?php echo admin_url('plugins.php'); ?>" class="button button-primary"><?php _e( 'Update the plugin', 'cuar' ); ?> &raquo;</a>
	</p>	
</div>