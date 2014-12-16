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

<?php 
	$sections = $this->get_status_sections();
	
	$attention_needed_messages = $this->plugin->get_attention_needed_messages();
	if ( $attention_needed_messages==null ) $attention_needed_messages = array();
	
	$i = 1;
	
	foreach ( $attention_needed_messages as $message_id => $message ) {	
		$message_handled = false;

		echo '<h3>' . sprintf( __('Issue #%d', 'cuar'), $i ) . '</h3>';
		
		foreach ( $sections as $section_id => $section ) {
			$linked_checks = isset( $section['linked-checks'] ) ? $section['linked-checks'] : array();
			
			if ( in_array( $message_id, $linked_checks ) ) {
				$template_path = isset( $section['template_path'] ) ? $section['template_path'] : CUAR_INCLUDES_DIR . '/core-addons/status';
				$template_file = 'status-attention-needed-' . $message_id . '.template.php';
				
				$template = $this->plugin->get_template_file_path( $template_path, $template_file, 'templates' );
				
				if ( !empty( $template ) ) {
                    /** @noinspection PhpIncludeInspection */
                    include( $template );
					
					$message_handled = true; 
					break;
				}
			}
		}
		
		if ( !$message_handled ) {
			echo '<div class="cuar-needs-attention"><p>' . $message['message'] . '</p></div>';
		}
		
		++$i;
	}
?>	

<?php 	if ( empty( $attention_needed_messages ) ) : ?>
	<div class="cuar-congrats">
		<p><?php _e('Congratulations, everything seems to be configured properly. You can enjoy your customer area now.', 'cuar'); ?></p>
	</div>
<?php 	endif; ?>