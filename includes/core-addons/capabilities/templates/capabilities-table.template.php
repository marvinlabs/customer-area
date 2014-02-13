<?php 
	require_once( CUAR_INCLUDES_DIR . '/helpers/wordpress-helper.class.php' );
	$column_count = 1; // We got an empty column on the left

	$selected_section_id = isset($_GET['cuar_section']) ? $_GET['cuar_section'] : 'cuar_general';
	$selected_section = $all_capability_groups[$selected_section_id];
	
	$section_links = array();
	foreach ( $all_capability_groups as $section_id => $section ) {
		$section_label = $section['label'];
		
		if ( $section_id!=$selected_section_id ) {
			$section_links[] = sprintf( '<a href="%1$s">%2$s</a>', 
					esc_attr( admin_url( 'admin.php?page=cuar-settings&cuar_tab=cuar_capabilities&cuar_section=' . $section_id ) ),
					$section_label );
		} else {
			$section_links[] = sprintf( '<span>%1$s</span>', $section_label );
		}
	}
?>

<div class="cuar_section_links">
	<?php echo implode( ' | ', $section_links ); ?>
</div>

<input type="hidden" name="cuar_section" value="<?php echo esc_attr( $selected_section_id ); ?>" />

<p>&nbsp;</p>
<div style="overflow-x: scroll; overflow-y: visible;">
<table class="widefat cuar-capabilities">
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

foreach ( $selected_section['groups'] as $group ) :
	$group_name = $group['group_name'];
	$group_caps = $group['capabilities'];
	
	if ( empty( $group_caps ) ) continue;	
?>	
	
		<tr><th colspan="<?php echo $column_count; ?>"><h3><?php echo $group_name; ?></h3></th></tr>
	
<?php
	foreach ( $group_caps as $cap => $cap_name ) : 
?>
		<tr>
			<th class="label"><?php echo $cap_name; ?></th>
<?php	foreach ( $all_roles as $role ) : 
			$id = str_replace( ' ', '-', $role->name . '_' . $cap );
			$checked = $role->has_cap( $cap ) ? 'checked="checked" ' : '';
			$readonly = $role->name=='administrator';
?>
			<td title="<?php echo esc_attr( $role->name . ' &raquo; ' . $cap_name ); ?>" class="value">
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
