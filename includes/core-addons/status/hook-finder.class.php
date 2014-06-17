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

if (!class_exists('CUAR_HookFinder')) :

/**
* Helps to scan a file to find hooks and actions
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_HookFinder {
	
	public function __construct() {
	}
	
	public function scan_file( $filepath ) {	
		$content = file_get_contents( $filepath );
		
		$this->find_hooks( $content, 'filter', $filepath );
		$this->find_hooks( $content, 'action', $filepath );
	}
	
	public function find_hooks( $input, $hook_type, $filepath ) {
		$hook_pos = strpos( $input, $hook_type=='filter' ? 'apply_filters' : 'do_action', 0 );

		while ( $hook_pos!=false ) {
			$name = $this->find_hook_name( $input, $hook_pos );
			
			if ( $name!=null ) {
				$name = $this->sanitize_hook_id( $name );
				
				$this->add_hook( $hook_type, $name, $filepath );
			}
			
			$hook_pos = strpos( $input, $hook_type=='filter' ? 'apply_filters' : 'do_action', $hook_pos + 1 );
		}
	}
	
	private function find_hook_name( $input, $hook_pos ) {
		$input_size = strlen( $input );
		$i = $hook_pos;
		$open_braces = 0;
		$name = '';
		$name_read = false;
		
		while ( $i<$input_size ) {
			$next_char = $input[$i];
			
			if ( empty( $name ) ) {
				// If we still have not begun the name building, wait until we find an opening brace
				if ( $next_char=='(' ) {
					$name .= $next_char;
					++$open_braces;
				}
			} else {
				if ( $next_char=='(') {
					++$open_braces;
				} else if ( $next_char==')' ) {
					--$open_braces;
				} else if ( $next_char==',' ) {
					$name_read = true;
					break;
				}
				
				// Stop if we have closed all braces
				if ( $open_braces==0 ) {
					$name_read = true;
					break;
				}
				
				$name .= $next_char;
			}
			
			++$i;
		}
		
		return $name_read ? $name : null;
	}
	
	public function scan_directory( $dir ) {		
		$items = scandir( $dir );
		
		foreach ( $items as $item ) {
			if ( $item=='.' || $item=='..' || $item=='hook-finder.class.php' ) continue; 
			
			$path = $dir . '/' . $item;
			
			if ( is_dir( $path ) ) {
				$this->scan_directory( $path );
			} else if ( false!==strpos( $item, '.php' ) ) {
				$this->scan_file( $path );
			}
		}
	}
	
	public function get_hook_count() {
		return count( $this->hooks );
	}
	
	public function print_hook_section( $title ) {
		echo '<tr>' . "\n";
		echo '  <td colspan="4"><h3>' . $title . '</h3></td>' . "\n";
		echo '</tr>' . "\n";
	}
	
	public function print_hook_headings() {
		echo '<tr>' . "\n";
		echo '  <th>' . __( 'Type', 'cuar' ) . '</th>' . "\n";
		echo '  <th>' . __( 'Name', 'cuar' ) . '</th>' . "\n";
		echo '  <th>' . __( 'File', 'cuar' ) . '</th>' . "\n";
		echo '  <th>' . __( 'Path', 'cuar' ) . '</th>' . "\n";
		echo '</tr>' . "\n";
	}
	
	public function print_hook_list() {
		// Sort hooks by name
		usort( $this->hooks, array( 'CUAR_HookFinder', 'sort_hooks_by_id' ) );
		
		// Display hooks
		foreach ( $this->hooks as $hook ) {
			echo '<tr class="cuar-' . esc_attr( $hook['type'] ) . '">' . "\n";
			echo '  <td class="cuar-' . esc_attr( $hook['type'] ) . '">' . $hook['type'] . '</td>' . "\n";
			echo '  <td class="">' . esc_html( $hook['id'] ) . '</td>' . "\n";
			echo '  <td class="">' . basename( $hook['filepath'] ) . '</td>' . "\n";
			echo '  <td class="">' . $this->get_short_directory( $hook['filepath'] ) . '</td>' . "\n";			
			echo '</tr>' . "\n";			
		}
	}
	
	private function add_hook( $type, $id, $filepath ) {	
		$this->hooks[] = array(
				'type'		=> $type,
				'id' 		=> $id,
				'filepath'	=> $filepath, 
			);
	} 
	
	private function sanitize_hook_id( $id ) {
        $sanitize_id = trim( $id, '( ' );

        $sanitize_id = str_replace('&', '&nbsp;&amp;&nbsp;', $sanitize_id );
		$sanitize_id = str_replace('/', '&nbsp;/&nbsp;', $sanitize_id );
		$sanitize_id = str_replace('?', '&nbsp;?&nbsp;', $sanitize_id );
		$sanitize_id = str_replace('=', '&nbsp;=&nbsp;', $sanitize_id );
		$sanitize_id = str_replace(' . ', '&nbsp;', $sanitize_id );
		$sanitize_id = str_replace('\'', '', $sanitize_id );
		$sanitize_id = str_replace('"', '', $sanitize_id );
		$sanitize_id = str_replace('  ', ' ', $sanitize_id );
		$sanitize_id = str_replace('  ', ' ', $sanitize_id );
		$sanitize_id = str_replace(' ', '&nbsp;', $sanitize_id );
		$sanitize_id = str_replace('&nbsp;&nbsp;', '&nbsp;', $sanitize_id );
		$sanitize_id = str_replace('&nbsp;&nbsp;', '&nbsp;', $sanitize_id );
			
        return $sanitize_id;		
	} 
	
	private function get_short_directory( $path ) {
		$dirname = dirname( $path );
		$strip_from = strpos( $path, 'customer-area' );
		$dirname = $strip_from>0 ? strstr( $dirname, 'customer-area' ) : $dirname;
		
		return $dirname;
	} 
	
	public static function sort_hooks_by_id( $a, $b ) {
	    if ($a['id'] == $b['id']) {
	        return 0;
	    }
	    return ($a['id'] < $b['id']) ? -1 : 1;
	}
	
	private $hooks = array();

	private static $REGEX = '/(apply_filters|do_action)\s*\(\s*[\'\"](.+?)\s*[,\)]/';
}

endif; // if (!class_exists('CUAR_HookFinder')) 
