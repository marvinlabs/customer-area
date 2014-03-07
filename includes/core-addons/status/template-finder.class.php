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

if (!class_exists('CUAR_TemplateFinder')) :

/**
* Helps to scan a file to find templates and their current version
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_TemplateFinder {
	
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}
	
	public function scan_directory( $dir ) {		
		$items = scandir( $dir );
		
		foreach ( $items as $item ) {
			if ( $item=='.' || $item=='..' ) continue; 
			
			$path = $dir . '/' . $item;
			
			if ( is_dir( $path ) ) {
				$this->scan_directory( $path );
			} else if ( false!==strpos( $item, '.template.php' ) ) {
				$this->add_file( $path );
			}
		}
	}
	
	public function get_outdated_templates() {
		$out = array();
		foreach ( $this->templates as $k => $t ) {
			if ( $t['is_outdated'] ) $out[$k] = $t;
		}		
		ksort( $out );
		return $out;
	}
	
	public function get_template_count() {
		return count( $this->templates );
	}
	
	public function print_template_section( $title ) {
		echo '<tr>' . "\n";
		echo '  <td colspan="4"><h3>' . $title . '</h3></td>' . "\n";
		echo '</tr>' . "\n";
	}
	
	public function print_template_headings() {
		echo '<tr>' . "\n";
		echo '  <th>' . __( 'File', 'cuar' ) . '</th>' . "\n";
		echo '  <th>' . __( 'Original', 'cuar' ) . '</th>' . "\n";
		echo '  <th>' . __( 'Current', 'cuar' ) . '</th>' . "\n";
		echo '</tr>' . "\n";
	}
	
	public function print_template_list() {
		ksort( $this->templates );
		
		foreach ( $this->templates as $name => $template ) {
			$is_outdated = $template['is_outdated'];
			$is_overriden = !empty( $template['current_path'] );
			
			$tr_class = $is_outdated ? 'cuar-outdated cuar-needs-attention' : '';
			$tr_class .= $is_overriden ? ' cuar-overriden' : '';
			
			echo '<tr class="' . $tr_class . '">' . "\n";
			echo '  <td class=""><code>' . basename( $template['original_path'] ) . '</code></td>' . "\n";
			echo '  <td class=""><strong>' 
						. sprintf( __( 'Version: %s', 'cuar' ), empty( $template['original_version'] ) ? __('unknown', 'cuar') : $template['original_version'] ) 
						. '</strong><br><em>' 
						. $this->get_short_directory( $template['original_path'], true ) 
						. '</em></td>' . "\n";
			if ( empty( $template['current_path'] ) ) {
				echo '  <td class="">' . __('Not overriden', 'cuar') . '</td>' . "\n";
			} else {
				echo '  <td class=""><strong>' 
							. sprintf( __( 'Version: %s', 'cuar' ), empty( $template['current_version'] ) ? __('unknown', 'cuar') : $template['current_version'] ) 
							. '</strong><br><em>' 
							. $this->get_short_directory( $template['current_path'], false ) 
							. '</em></td>' . "\n";
			}
			echo '</tr>' . "\n";			
		}
	}
	
	private function add_file( $filepath ) {
		// Get original version
		$original_version = $this->get_template_version( $filepath );
		
		// Get overloaded version number
		$current_path = $this->plugin->get_template_file_path( pathinfo( $filepath, PATHINFO_DIRNAME ), basename( $filepath ), 'templates' );
		$current_version = empty( $current_path ) ? '' : $this->get_template_version( $current_path );
		
		// Outdated?
		if ( empty( $current_version ) && !empty( $original_version ) && !empty( $current_path ) ) {
			$is_outdated = true;
		} else if ( empty( $current_version ) ) {
			$is_outdated = false;
		} else {
			$is_outdated = version_compare( $original_version, $current_version, '!=' );
		}
		
		// Add template to our list
		$name = basename( $filepath );
		
		$this->templates[ $name ] = array(
				'original_path'		=> $filepath,
				'original_version'	=> $original_version,
				'current_path'		=> $current_path,
				'current_version'	=> $current_version,
				'is_outdated'		=> $is_outdated
			);
	}
	
	private function get_template_version( $filepath ) {
		$input = file_get_contents( $filepath, null, null, null, 256 );
		
		if ( preg_match( self::$VERSION_REGEX, $input, $matches )>0 ) {
			return $matches[1];
		}
		
		return '';
	}
	
	private function get_short_directory( $path, $is_original ) {
		$dirname = dirname( $path );
		
		if ( $is_original ) {
			$strip_from = strpos( $path, 'customer-area' );
			$dirname = $strip_from>0 ? strstr( $dirname, 'customer-area' ) : $dirname;
		} else {
			$strip_from = strpos( $path, 'wp-content' );
			$dirname = $strip_from>0 ? strstr( $dirname, 'wp-content' ) : $dirname;
		}
		
		return $dirname;
	} 
	
	private $templates = array();

	private static $VERSION_REGEX = '/Template version:\s*(\d+\.\d+.\d+)/';
}

endif; // if (!class_exists('CUAR_TemplateFinder')) 
