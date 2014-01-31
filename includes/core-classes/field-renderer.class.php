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

if (!class_exists('CUAR_FieldRenderer')) :

/**
 * Class that helps rendering fields in a form (like profile fields) 
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_FieldRenderer {

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}
	
	public function start_section( $id, $label ) {
		// Just in case
		$this->end_section();
		
		if ( !isset( $this->sections[$id] ) ) {		
			$this->sections[$id] = array(
					'id'		=> $id,
					'label'		=> $label,
					'fields'	=> array()
				);	
		}
		
		$this->current_section_id = $id;
		$this->current_fields = $this->sections[$id]['fields'];
	}
	
	public function end_section() {
		if ( $this->current_section_id!=null ) {
			$this->sections[ $this->current_section_id ][ 'fields' ] = $this->current_fields;
		}
		
		$this->current_section_id = null;
		$this->current_fields = array();
	}
	
	public function add_simple_field( $id, $label, $value, $placeholder=false, $class='default' ) {
		if ( empty( $this->sections ) ) {
			$this->start_section( 'default', '' );	
		}
		
		$this->current_fields[] = $this->build_simple_field( $id, $label, $value, $placeholder, $class );
	}
	
	public function print_fields() {
		// Just in case
		$this->end_section();

		$this->print_header();
		
		foreach ( $this->sections as $section ) {
			$this->print_section_header( $section );
			
			$fields = $section[ 'fields' ];
			foreach ( $fields as $field ) {
				$template = $this->get_field_template( $field );
				include( $template );
			}
			
			$this->print_section_footer( $section );
		}

		$this->print_footer();
	}
	
	protected function print_section_header( $section ) {
		echo '<div class="cuar-field-section cuar-field-section-' . $section['id'] . '">';
		if ( !empty( $section['label'] ) ) {
			echo '<h3 class="cuar-section-title">' . $section['label'] . '</h3>';
		}			
	}
	
	protected function print_section_footer( $section ) {
		echo '</div>';
	}
	
	protected function print_header() {		
	}
	
	protected function print_footer() {		
	}
	
	protected function build_simple_field( $id, $label, $value, $placeholder=false, $class='default' ) {
		return array(
				'editable'			=> false,
				'id'				=> $id,
				'label' 			=> $label,
				'value'				=> $value,
				'classes'			=> explode( ' ', $class ),
				'placeholder'		=> $placeholder
			);
	}
	
	protected function get_field_template( $field ) {
		$classes = $field[ 'classes' ];
		
		// If our template is in cache, return the first one we find
		foreach ( $classes as $class ) {
			if ( isset( $this->field_templates[$class] ) ) {
				$template = $this->field_templates[$class];			
				if ( !empty( $template ) ) return $template;
			} else {
				$file_name = $class=='default' || empty( $class ) ? 'field.template.php' : 'field-' . $class . '.template.php';				
				$template = $this->plugin->get_template_file_path(
						CUAR_INCLUDES_DIR . '/core-classes',
						$file_name,
						'templates' 
					);
				
				if ( !empty( $template ) ) {
					$this->field_templates[ $class ] = $template;
					return $template;
				}
			}
		}
		
		return $this->plugin->get_template_file_path(
						CUAR_INCLUDES_DIR . '/core-classes',
						'field.template.php',
						'templates' 
					);
	}
	
	protected $field_templates = array();	
	protected $sections = array();
	protected $current_fields = array();
	protected $current_section_id = null;
	protected $plugin = null;
}

endif; // if (!class_exists('CUAR_FieldRenderer')) 
