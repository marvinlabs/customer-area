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
 * Class that helps rendering fields (like profile fields) 
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_FieldRenderer {

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}
	
	public function print_fields( $is_form = false ) {
		// Just in case
		$this->end_section();
		
		foreach ( $this->sections as $section ) {
			$fields = $section[ 'fields' ];
			
			echo '<div class="cuar-field-section cuar-field-section-' . $section['id'] . '">';
			
			if ( !empty( $section['label'] ) ) {
				echo '<h3>' . $section['label'] . '</h3>';
			} 
			
			foreach ( $fields as $field ) {
				$template = $this->get_field_template( $field );
				include( $template );
			}
			
			echo '</div>';
		}
	}
	
	public function start_section( $id, $label ) {
		$this->sections[ $id ] = array(
				'id'		=> $id,
				'label'		=> $label,
				'fields'	=> array()
			);
			
		$this->current_section_id = $id;
		$this->current_fields = array();
	}
	
	public function end_section() {
		if ( $this->current_section_id!=null ) {
			$this->sections[ $this->current_section_id ][ 'fields' ] = $this->current_fields;
		}
		
		$this->current_section_id = null;
		$this->current_fields = array();
	}
	
	public function add_field( $id, $label, $value, $placeholder=false, $class='default' ) {
		if ( empty( $this->sections ) ) {
			$this->start_section( 'default', '' );	
		}
		
		$this->current_fields[] = array(
				'editable'			=> false,
				'id'				=> $id,
				'label' 			=> $label,
				'value'				=> $value,
				'class'				=> $class,
				'placeholder'		=> $placeholder
			);
	}
	
	public function add_editable_field( $id, $label, $value, $placeholder=false, $class='default', $control_name, $control_type='text', $available_values=null ) {
		if ( empty( $this->sections ) ) {
			$this->start_section( 'cuar-default', '' );	
		}
		
		$this->current_fields[] = array(
				'editable'			=> true,
				'id'				=> $id,
				'label' 			=> $label,
				'value'				=> $value,
				'class'				=> $class,
				'placeholder'		=> $placeholder,
				'control_type'		=> $control_type,
				'control_name'		=> $control_name,
				'available_values'	=> $available_values
			);
	}
	
	protected function get_field_template( $field ) {		
		$class = $field[ 'class' ];
		if ( !isset( $this->field_templates[ $class ] ) ) {
			$this->field_templates[ $class ] = $this->plugin->get_template_file_path(
				CUAR_INCLUDES_DIR . '/core-classes',
				'field-' . $class . '.template.php',
				'templates',
				'field.template.php' );
		}
		
		return $this->field_templates[ $class ];
	}
	
	protected $field_templates = array();	
	protected $sections = array();
	protected $current_fields = array();
	protected $current_section_id = null;
	protected $plugin = null;
}

endif; // if (!class_exists('CUAR_FieldRenderer')) 
