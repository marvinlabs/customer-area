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

<?php 
wp_enqueue_script('common');
wp_enqueue_script('wp-lists');
wp_enqueue_script( 'postbox' ); 

global $screen_layout_columns;
?>

<div class="wrap cuar-dashboard">
	<?php screen_icon(); ?>
	
	<h2><?php _e( 'Customer Area' , 'cuar' ); ?></h2>

	<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
		<div class="cuar-main-half">
			<?php do_meta_boxes( 'customer-area', 'normal', array( 'echo'	=> true ) ); ?>
		</div>
		<div class="cuar-main-half-2">
			<?php do_meta_boxes( 'customer-area', 'side', array( 'echo'	=> true ) ); ?>
		</div>
		<div class="cuar-side">
			<div class="dashboard-logo"><img src="<?php echo $this->plugin->get_admin_theme_url(); ?>/images/logo.png" /></div>
		</div>
		<br class="clear"/>							
	</div>	
</div>

<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
	});
	//]]>
</script>