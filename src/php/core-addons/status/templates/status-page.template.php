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
?>

<div class="wrap cuar-plugin-status">
	<h1>WP Customer Area <small><sup><?php echo $this->plugin->get_version(); ?></sup></small></h1>
	
<?php 
	$sections = $this->get_status_sections();
	$current_section = isset( $_GET['tab'] ) ? $_GET['tab'] : 'needs-attention';
   	$current_section = $sections[$current_section];
?>
	
	<h2 class="nav-tab-wrapper">
<?php 	
	foreach ( $sections as $section_id => $section ) {
		if ( !isset( $section['label'] ) ) continue;
		
		$section_label = $section['label'];
		$is_current = $section_id==$current_section['id'];

		printf( '<a href="%1$s" class="nav-tab %2$s">%3$s</a>',
						esc_attr( admin_url( 'admin.php?page=' . self::$PAGE_SLUG . '&tab=' . $section_id ) ),
						$is_current ? 'nav-tab-active' : '',
						esc_html( $section_label ) );
	}
?>
	</h2>
	
	<div class="cuar-status-section">	
		<h2><?php echo $current_section['title'] ?></h2>
	
		<form method="POST" action="" enctype="multipart/form-data">
			<input type="hidden" name="cuar-do-status-action" value="1" />		
<?php
		do_action( 'cuar/templates/status/before-section?id=' . $current_section['id'] );		
		$this->print_section_template( $current_section );		
		do_action( 'cuar/templates/status/after-section?id=' . $current_section['id'] );
?>	
		</form>
	</div>
</div>