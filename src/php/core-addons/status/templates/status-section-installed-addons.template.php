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

$plugin_version = cuar()->get_version();
$version_matrix = json_decode(file_get_contents(CUAR_PLUGIN_DIR . '/versions.json'), true);

if ( !isset($version_matrix[$plugin_version])) return array();

$recommended_versions = $version_matrix[$plugin_version];

// List plugins
$all_plugins = get_plugins();
?>

<p>
	<a href="http://wp-customerarea.com/my-account" class="button button-primary"><?php _e( 'Get latest add-ons from your account', 'cuar' ); ?> &raquo;</a>

	<input type="submit" id="cuar-refresh-version-mismatch" name="cuar-refresh-version-mismatch" class="button" value="<?php esc_attr_e('Check versions again', 'cuar'); ?> &raquo;"/>
	<?php wp_nonce_field('cuar-refresh-version-mismatch', 'cuar-refresh-version-mismatch_nonce'); ?>
</p>

<table class="widefat cuar-status-table">
	<thead>
		<tr>
            <th></th>
			<th><?php _e( 'Name', 'cuar' ); ?></th>
			<th><?php _e( 'Current version', 'cuar' ); ?></th>
			<th><?php _e( 'Recommended version', 'cuar' ); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	foreach ($all_plugins as $id => $plugin_info) :
		$plugin_name = explode('/', $id);
		$plugin_name = $plugin_name[0];

		if ( !isset($recommended_versions[$plugin_name])) continue;

		$is_active = is_plugin_active($plugin_name . '/' . $plugin_name . '.php');
		$is_mismatch = version_compare($plugin_info['Version'], $recommended_versions[$plugin_name], '<');
		$tr_class = $is_mismatch ? 'cuar-needs-attention ' : '';
        $tr_class = $is_active ? $tr_class . 'cuar-is-active ' : $tr_class . 'cuar-is-inactive ';
	?>
		<tr class="<?php echo $tr_class; ?>">
            <td>
                <?php if ($is_mismatch): ?>
                    <span class="dashicons dashicons-warning" title="<?php esc_attr_e( 'Version mismatch', 'cuar' ); ?>"></span>
                <?php elseif ($is_active): ?>
                    <span class="dashicons dashicons-yes" title="<?php esc_attr_e( 'Addon is active', 'cuar' ); ?>"></span>
                <?php else: ?>
                    <span class="dashicons dashicons-minus" title="<?php esc_attr_e( 'Addon is not active', 'cuar' ); ?>"></span>
                <?php endif; ?>
            </td>
			<td><?php echo $plugin_info['Name']; ?></td>
			<td><?php echo $plugin_info['Version']; ?></td>
			<td><?php echo $recommended_versions[$plugin_name]; ?></td>
		</tr>
<?php 
	endforeach; ?>
	</tbody>
</table>