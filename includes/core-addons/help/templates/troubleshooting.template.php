<h1><?php _e( 'Getting support', 'cuar' ); ?></h1>

<p><strong><?php _e( "Please note that we do not reply to support requests sent by email.", 'cuar' ); ?></strong> 
<?php _e( 'If you have any problem, we have some resources that could help you:', 'cuar' ); ?></p>
	
<ul>
	<li><?php printf( __( 
			'<a href="%1$s" target="_blank">Documentation</a> for the main plugin and for the add-ons.', 'cuar' ),
		'http://customer-area.marvinlabs.com/documentation/'); ?></li>
	<li><?php printf( __( 
			'<a href="%1$s" target="_blank">Frequently Asked Questions</a>. ', 'cuar' ),
		'http://wordpress.org/plugins/customer-area/faq/'); ?></li>
	<li><?php printf( __( 
			'A dedicated <a href="%1$s" target="_blank">wordpress.org support forum</a>, only for the main plugin (English only as per the wordpress.org rules).', 'cuar' ),
		'http://wordpress.org/support/plugin/customer-area'); ?></li>
	<li><?php printf( __( 
			'<a href="%1$s" target="_blank">Support forums</a> on our website for the main plugin as well as for the add-ons (English, French and Spanish are ok there). ', 'cuar' ),
		'http://customer-area.marvinlabs.com/support/'); ?></li>
</ul>

<h1><?php _e( 'Reset settings', 'cuar' ); ?></h1>

<p><?php _e('Pressing the button below will reset all your Customer Area settings to the default values. This cannot be undone!', 'cuar' ); ?></p>

<form method="POST" action="">
	<p >
		<input type="submit" name="cuar-reset-all-settings" id="cuar-reset-all-settings" class="button button-primary" value="<?php esc_attr_e( 'Reset default settings', 'cuar' ); ?>" />
	</p>
</form>

<h1><?php _e( 'Troubleshooting information', 'cuar' ); ?></h1>

<h2><?php _e( 'Installed add-ons', 'cuar' ); ?></h2>

<table class="widefat">
	<thead>
		<tr>
			<th><?php _e( 'Name', 'cuar' ); ?></th>
			<th><?php _e( 'Required customer area version', 'cuar' ); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
	global $cuar_plugin;
	$addons =  $cuar_plugin->get_registered_addons();
	foreach ( $addons as $id => $addon ) : ?>
		<tr>
			<td><?php echo $addon->addon_name; ?></td>
			<td><?php echo $addon->min_cuar_version; ?></td>
		</tr>
<?php 
	endforeach; ?>
	</tbody>
</table>
<p>&nbsp;</p>
