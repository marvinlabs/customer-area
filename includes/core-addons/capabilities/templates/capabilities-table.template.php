<?php 
require_once( CUAR_INCLUDES_DIR . '/helpers/wordpress-helper.class.php' );
$column_count = 1; // We got an empty column on the left
?>
<p>&nbsp;</p>
<div style="overflow-x: scroll; overflow-y: visible;">
<table class="widefat">
	<thead>
		<tr>
			<th></th>
<?php 		foreach ( $all_roles as $role ) : 
				++$column_count; 
?>
			<th><?php echo CUAR_WordPressHelper::getRoleDisplayName( $role->name ); ?></th>		
<?php 		endforeach; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th></th>
<?php 		foreach ( $all_roles as $role ) : ?>
			<th><?php echo CUAR_WordPressHelper::getRoleDisplayName( $role->name ); ?></th>			
<?php 		endforeach; ?>
		</tr>
	</tfoot>
	<tbody>
	
<?php

foreach ( $all_capability_groups as $group ) :
	$group_name = $group['group_name'];
	$group_caps = $group['capabilities'];
	
	if ( empty( $group_caps ) ) continue;	
?>	
	
		<tr><th colspan="<?php echo $column_count; ?>"><h3><?php echo $group_name; ?></h3></th></tr>
	
<?php
	foreach ( $group_caps as $cap => $cap_name ) : 
?>
		<tr>
			<th><?php echo $cap_name; ?></th>
<?php	foreach ( $all_roles as $role ) : 
			$id = str_replace( ' ', '-', $role->name . '_' . $cap );
			$checked = $role->has_cap( $cap ) ? 'checked="checked" ' : '';
			$readonly = $role->name=='administrator';
?>
			<td title="<?php echo esc_attr( $role->name . ' &raquo; ' . $cap_name ); ?>">
<?php 		if ( $readonly ) : ?>
<?php 			if ( !empty( $checked ) ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $id ); ?>" value="1" />
<?php 			endif; ?>
				<input type="checkbox" name="dummy_<?php echo esc_attr( $id ); ?>" value="1" <?php echo $checked; ?>disabled="disabled" />
<?php 		else : ?>			
				<input type="checkbox" name="<?php echo esc_attr( $id ); ?>" <?php echo $checked; ?>value="1" />
<?php 		endif; ?>
			</td>
<?php 	endforeach; // Roles ?>
		</tr>
<?php	
	endforeach; // Caps ?>
	</tbody>
<?php
endforeach; // Plugins
?>
</table>
</div>
