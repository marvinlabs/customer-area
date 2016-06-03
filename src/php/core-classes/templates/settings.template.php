<?php /** Template version: 1.1.0 */ ?>

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
	<h1>WP Customer Area <small><sup><?php echo $this->plugin->get_version(); ?></sup></small></h1>

	<h2 class="nav-tab-wrapper">
<?php foreach ( $this->tabs as $tab_id => $tab_label) : ?>
			<?php  printf( '<a href="options-general.php?page=%s&tab=%s" class="nav-tab %s">%s</a>',
						CUAR_Settings::$OPTIONS_PAGE_SLUG,
						$tab_id,
						( $this->current_tab == $tab_id ? 'nav-tab-active' : '' ),
						esc_html( $tab_label ) ); ?>
<?php endforeach; ?>
	</h2>
	
	<div class="cuar-main cuar-settings">
	
<?php $this->plugin->print_admin_notices(); ?>	
	
<?php do_action( 'cuar/templates/settings/before-settings', $this ); ?>
<?php do_action( 'cuar/templates/settings/before-settings?tab=' . $this->current_tab, $this ); ?>
	
	<form method="post" action="options.php"> 	
		<input type="hidden" id="tab" name="tab" value="<?php echo $this->current_tab; ?>" />
		<input type="hidden" id="cuar_do_save_settings" name="cuar_do_save_settings" value="1" />

	<?php 
		settings_fields( CUAR_Settings::$OPTIONS_GROUP . '_' . $this->current_tab ); 
		do_settings_sections( CUAR_Settings::$OPTIONS_PAGE_SLUG );
		do_action( 'cuar/templates/settings/in-settings-form?tab=' . $this->current_tab, $this );
	?>
	
	<?php submit_button(); ?>
	</form>
	
<?php do_action( 'cuar/templates/settings/after-settings?tab=' . $this->current_tab, $this ); ?>
<?php do_action( 'cuar/templates/settings/after-settings', $this ); ?>

	</div>
</div>

