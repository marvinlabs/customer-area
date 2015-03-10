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
$dirs_to_scan = apply_filters( 'cuar/core/status/directories-to-scan', array( CUAR_PLUGIN_DIR => __( 'WP Customer Area', 'cuar' ) ) );
?>

<div>
	<a href="#" id="cuar_show_all"><?php _e('All', 'cuar' ); ?></a> |
	<a href="#" id="cuar_show_overridden"><?php _e('Overridden only', 'cuar' ); ?></a> |
	<a href="#" id="cuar_show_outdated"><?php _e('Outdated only', 'cuar' ); ?></a>
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
			$template_finder = new CUAR_TemplateFinder( $this->plugin->get_template_engine() );
			$template_finder->scan_directory( $dir );


            include_once( CUAR_INCLUDES_DIR . '/core-addons/status/template-printer.class.php' );
            $template_printer = new CUAR_TemplatePrinter();
?>
	<div id="section_tab_<?php echo esc_attr( sanitize_title( $title ) ); ?>">	
		<h2><?php printf( __( '%s: %d template(s)', 'cuar' ), $title, $template_finder->get_template_count() ); ?></h2>
		<table class="widefat cuar-templates-table">
<?php
            $template_printer->print_template_headings();
            $template_printer->print_template_list($template_finder->get_all_templates());
?>
		</table>
	</div>
		
<?php 	endforeach; ?>
</div>



<script type="text/javascript">
	jQuery(document).ready(function($) {
    	$( "#sections_tabs" ).tabs();
    	
    	$('#cuar_show_all').click(function() {
    		$('.cuar-templates-table tr').show();
    	});
    	$('#cuar_show_overridden').click(function() {
    		$('.cuar-templates-table tr').hide();
    		$('.cuar-templates-table tr.cuar-overridden').show();
    	});
    	$('#cuar_show_outdated').click(function() {
    		$('.cuar-templates-table tr').hide();
    		$('.cuar-templates-table tr.cuar-outdated').show();
    	});
	});
</script>