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

wp_enqueue_script( 'jquery-ui-tabs' );

include( CUAR_INCLUDES_DIR . '/core-addons/status/hook-finder.class.php' );
$dirs_to_scan = apply_filters( 'cuar/core/status/directories-to-scan', array( CUAR_PLUGIN_DIR => __( 'WP Customer Area', 'cuar' ) ) );

?>

<div>
	<a href="#" id="cuar_show_all"><?php _e('All', 'cuar' ); ?></a> |
	<a href="#" id="cuar_show_filters"><?php _e('Filters only', 'cuar' ); ?></a> |
	<a href="#" id="cuar_show_actions"><?php _e('Actions only', 'cuar' ); ?></a>
</div>

<p>&nbsp;</p>
 
<div id="sections_tabs" class="tab-container">
	<ul class="tab-wrapper">
<?php 	
	foreach ( $dirs_to_scan as $dir => $title ) {	
		printf( '<li class="nav-tab"><a href="#section_tab_%1$s">%2$s</a></li>',
						sanitize_title( $title ),
						esc_html( $title ) );
	}
?>
	</ul>
	
<?php 	foreach ( $dirs_to_scan as $dir => $title ) : 
			$hook_finder = new CUAR_HookFinder();
			$hook_finder->scan_directory( $dir );
?>
	<div id="section_tab_<?php echo esc_attr( sanitize_title( $title ) ); ?>">	
		<h2><?php printf( __( '%s: %d hook(s)', 'cuar' ), $title, $hook_finder->get_hook_count() ); ?></h2>
		<table class="widefat cuar-hooks-table">
<?php 			
			$hook_finder->print_hook_headings();
			$hook_finder->print_hook_list();
?>
		</table>
	</div>
		
<?php 	endforeach; ?>
</div>


<script type="text/javascript">
	jQuery(document).ready(function($) {
    	$( "#sections_tabs" ).tabs();
    	
		$('#cuar_show_all').click(function() {
			$('.cuar-hooks-table tr.cuar-filter').show();
			$('.cuar-hooks-table tr.cuar-action').show();
		});
		$('#cuar_show_filters').click(function() {
			$('.cuar-hooks-table tr.cuar-filter').show();
			$('.cuar-hooks-table tr.cuar-action').hide();
		});
		$('#cuar_show_actions').click(function() {
			$('.cuar-hooks-table tr.cuar-filter').hide();
			$('.cuar-hooks-table tr.cuar-action').show();
		});
	});
</script>