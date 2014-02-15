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

require_once( CUAR_INCLUDES_DIR . '/core-classes/addon-page.class.php' );

if (!class_exists('CUAR_PrivateFileCategoriesWidget')) :

/**
 * Widget to show the terms of a taxonomy in a list
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class CUAR_TermsWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct(  $id_base, $name, $widget_options = array(), $control_options = array() ) {
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}
	
	protected abstract function get_link( $term );
	
	protected abstract function get_taxonomy();
	
	protected abstract function get_default_title();

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		// Don't output anything if we don't have any categories or if the user is a guest
		if ( !is_user_logged_in() ) return;
		
		$categories = get_terms( $this->get_taxonomy(), array(
				'parent'		=> 0,
				'hide_empty'	=> 0
			) );
		if ( count( $categories )<=0 ) return;
		
		echo $args['before_widget'];
		
		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		$this->print_category_list( $categories );
		
		echo $args['after_widget'];
	}
	
	private function print_category_list( $categories ) {		
		echo '<ul>';
		
		foreach ( $categories as $cat ) {
			echo '<li>';
			
			$link = $this->get_link( $cat );
			
			printf( '<a href="%1$s" title="%3$s">%2$s</a>',
					$link,
					$cat->name,
					sprintf( esc_attr__('Show all content categorized under %s', 'cuar' ), $cat->name )					 
				);
			
			$children = get_terms( 'cuar_private_file_category', array(
					'parent'		=> $cat->term_id,
					'hide_empty'	=> 0
				));
			
			if ( count( $children )>0 ) {
				$this->print_category_list( $children );
			}

			echo '</li>';
		}
		
		echo '</ul>';		
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = $this->get_default_title();
		}
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'cuar' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}

endif; // if (!class_exists('CUAR_PrivateFileCategoriesWidget')) 
