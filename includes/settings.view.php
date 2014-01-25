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

<div class="wrap cuar-settings-<?php echo $this->current_tab; ?>">
	<?php screen_icon( 'icon32-settings' ); ?>
	
	<h2 class="nav-tab-wrapper">
<?php foreach ( $this->tabs as $tab_id => $tab_label) : ?>
			<?php  printf( '<a href="?page=%s&cuar_tab=%s" class="nav-tab %s">%s</a>',
						CUAR_Settings::$OPTIONS_PAGE_SLUG,
						$tab_id,
						( $this->current_tab == $tab_id ? 'nav-tab-active' : '' ),
						esc_html( $tab_label ) ); ?>
<?php endforeach; ?>
	</h2>
	
	<div class="cuar-main cuar-settings">
	
<?php do_action( 'cuar_before_settings', $this ); ?>
<?php do_action( 'cuar_before_settings_' . $this->current_tab, $this ); ?>
	
	<form method="post" action="options.php"> 	
		<input type="hidden" id="cuar_tab" name="cuar_tab" value="<?php echo $this->current_tab; ?>" />
	
	<?php 
		settings_fields( CUAR_Settings::$OPTIONS_GROUP . '_' . $this->current_tab ); 
		do_settings_sections( CUAR_Settings::$OPTIONS_PAGE_SLUG ); 
	?>
	
<?php do_action( 'cuar_in_settings_form_' . $this->current_tab, $this ); ?>
	
	<?php submit_button(); ?>
	</form>
	
<?php do_action( 'cuar_after_settings_' . $this->current_tab, $this ); ?>
<?php do_action( 'cuar_after_settings', $this ); ?>

	</div>
	
	<div class="cuar-side">

	<?php do_action( 'cuar_before_settings_side', $this ); ?>
	<?php do_action( 'cuar_settings_side_' . $this->current_tab, $this ); ?>
	<?php do_action( 'cuar_after_settings_side', $this ); ?>
		
	</div>
</div>

