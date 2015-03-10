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

if (!class_exists('CUAR_AbstractFieldRenderer')) :

include_once( CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/field-renderer.interface.php' );

/**
 * The base class for field renderers
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class CUAR_AbstractFieldRenderer implements CUAR_FieldRenderer {

	/*------- ACCESSORS ---------------------------------------------------------------------------------------------*/

	// See CUAR_FieldRenderer
	public function get_label() {
		return $this->label;
	}
	
	public function get_inline_help() {
		return $this->inline_help;
	}
	
	public function initialize( $label, $inline_help='' ) {
		$this->label = $label;
		$this->inline_help = $inline_help;		
		return $this;
	}
	
	protected $label = '';	
	protected $inline_help = '';	

	// See CUAR_FieldRenderer
	public function render_raw_value( $value ) {
		echo $value;
	}
	
	/*------- READ-ONLY DISPLAY -------------------------------------------------------------------------------------*/
	
	// See CUAR_FieldRenderer
	public function render_read_only_field( $id, $value ) {
		$out = '<div class="cuar-field cuar-readonly-field cuar-' . $this->get_type() . '-field">' . "\n";
				
		$out .= apply_filters( 'cuar/core/fields/readonly/before-label?type=' . $this->get_type(), 	$this->get_before_readonly_field_label( $id, $value ), 	$this );
		$out .= apply_filters( 'cuar/core/fields/readonly/label?type=' . $this->get_type(), 		$this->get_readonly_field_label( $id, $value ), 		$this );
		$out .= apply_filters( 'cuar/core/fields/readonly/after-label?type=' . $this->get_type(), 	$this->get_after_readonly_field_label( $id, $value ), 	$this );
		$out .= "\n";
		
		$out .= apply_filters( 'cuar/core/fields/readonly/before-value?type=' . $this->get_type(), 	$this->get_before_readonly_field_value( $id, $value ), 	$this );
		$out .= apply_filters( 'cuar/core/fields/readonly/value?type=' . $this->get_type(), 		$this->get_readonly_field_value( $id, $value ), 		$this );
		$out .= apply_filters( 'cuar/core/fields/readonly/after-value?type=' . $this->get_type(), 	$this->get_after_readonly_field_value( $id, $value ), 	$this );
		$out .= "\n";		
		
		$out .= '</div>' . "\n";
		
		echo $out;
	}
	
	protected function get_readonly_field_label( $id, $value ) {
		return $this->get_label();
	}
	
	protected function get_before_readonly_field_label( $id, $value ) {
		return '<div class="cuar-field-label">';
	}
	
	protected function get_after_readonly_field_label( $id, $value ) {
		return '</div>';
	}
	
	protected abstract function get_readonly_field_value( $id, $value );

	protected function get_before_readonly_field_value( $id, $value ) {
		return '<div class="cuar-field-value">';
	}

	protected function get_after_readonly_field_value( $id, $value ) {
		return '</div>';
	}

	/*------- FORM INPUT DISPLAY ------------------------------------------------------------------------------------*/

	// See CUAR_FieldRenderer
	public function render_form_field( $id, $value ) {
		
		$out = '<div class="form-group cuar-field cuar-form-field cuar-' . $this->get_type() . '-field">' . "\n";
				
		$out .= apply_filters( 'cuar/core/fields/form/before-label?type=' . $this->get_type(), 	$this->get_before_form_field_label( $id, $value ), 	$this );
		$out .= apply_filters( 'cuar/core/fields/form/label?type=' . $this->get_type(), 		$this->get_form_field_label( $id, $value ), 		$this );
		$out .= apply_filters( 'cuar/core/fields/form/after-label?type=' . $this->get_type(), 	$this->get_after_form_field_label( $id, $value ), 	$this );
		$out .= "\n";
		
		$out .= apply_filters( 'cuar/core/fields/form/before-input?type=' . $this->get_type(), 	$this->get_before_form_field_input( $id, $value ), 	$this );
		$out .= apply_filters( 'cuar/core/fields/form/input?type=' . $this->get_type(), 		$this->get_form_field_input( $id, $value ), 		$this );		
		$out .= apply_filters( 'cuar/core/fields/form/inline-help?type=' . $this->get_type(), 	$this->get_form_inline_help( $id, $value ), 		$this );		
		$out .= apply_filters( 'cuar/core/fields/form/after-input?type=' . $this->get_type(), 	$this->get_after_form_field_input( $id, $value ), 	$this );
		$out .= "\n";
		
		$out .= '</div>' . "\n";
		
		echo $out;
	}
	
	protected function get_form_inline_help( $id, $value ) {
		$ih = $this->get_inline_help();
		
		if ( empty( $ih ) ) return ''; 
		return '<span class="help-block">' . $ih . '</span>';
	}
	
	protected function get_form_field_label( $id, $value ) {
		return $this->get_label();
	}
	
	protected function get_before_form_field_label( $id, $value ) {
		return sprintf( '<label for="%1$s" class="control-label">', $id );
	}
	
	protected function get_after_form_field_label( $id, $value ) {
		return '</label>';
	}
	
	protected abstract function get_form_field_input( $id, $value );

	protected function get_before_form_field_input( $id, $value ) {
		return '<div class="control-container">';
	}

	protected function get_after_form_field_input( $id, $value ) {
		return '</div>';
	}
}

endif; // if (!class_exists('CUAR_AbstractFieldRenderer')) :