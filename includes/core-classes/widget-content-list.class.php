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

if (!class_exists('CUAR_ContentListWidget')) :

/**
 * Widget to show the dates of content 
*
* @author Vincent Prat @ MarvinLabs
*/
abstract class CUAR_ContentListWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct(  $id_base, $name, $widget_options = array(), $control_options = array() ) {
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}
	
	protected abstract function get_post_type();
	
	protected abstract function get_default_title();
	
	protected abstract function get_default_no_content_message();
	
	protected function get_associated_taxonomy() {
		return null;
	}
	
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
		
		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$posts = $this->get_content( $args, $instance );
		if ( count( $posts )<=0 ) {
			echo '<p>' . isset( $instance[ 'no_content_message' ] ) ? $instance[ 'no_content_message' ] : $this->get_default_no_content_message() . '</p>';
		} else {
			$this->print_content_list( $posts );
		}
		
		echo $args['after_widget'];
	}
	
	function get_content( $args, $instance ) {			
		// Get user content
		$cuar_plugin = CUAR_Plugin::get_instance();
		$po_addon = $cuar_plugin->get_addon( 'post-owner' );
		
		$limit = isset( $instance[ 'posts_per_page' ] ) ? $instance[ 'posts_per_page' ] : 5;
		$orderby = isset( $instance[ 'orderby' ] ) ? $instance[ 'orderby' ] : 'date';
		$order = isset( $instance[ 'order' ] ) ? $instance[ 'order' ] : 'DESC';
		$category = isset( $instance[ 'category' ] ) ? $instance[ 'category' ] : -1;
		
		$args = array(
				'post_type' 		=> $this->get_post_type(),
				'posts_per_page' 	=> $limit,
				'orderby' 			=> $orderby,
				'order' 			=> $order,
				'meta_query' 		=> $po_addon->get_meta_query_post_owned_by( get_current_user_id() )
			);	

		if ( $category>0 ) {
			$args[ 'tax_query' ] = array(
					array(
							'taxonomy' 		=> $this->get_associated_taxonomy(),
							'field' 		=> 'id',
							'terms' 		=> $category
						)
				);
		}		

		$args = apply_filters( 'cuar_widget_query_parameters-' .  $this->id_base, $args );
		$posts = get_posts( $args );
		
		return $posts;
	}
	
	private function print_content_list( $posts ) {		
		echo '<ul>';
		
		foreach ( $posts as $post ) {
			echo '<li>';

			$link = get_permalink( $post );
			$title = get_the_title( $post );
			
			printf( '<a href="%1$s" title="%3$s">%2$s</a>',
					$link,
					$title,
					sprintf( esc_attr__('Link to %s', 'cuar' ), $title )					 
				);

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
		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : $this->get_default_title();
		$no_content_message = isset( $instance[ 'no_content_message' ] ) ? $instance[ 'no_content_message' ] : $this->get_default_no_content_message();
		$posts_per_page = isset( $instance[ 'posts_per_page' ] ) ? $instance[ 'posts_per_page' ] : 5;
		$orderby = isset( $instance[ 'orderby' ] ) ? $instance[ 'orderby' ] : 'date';
		$order = isset( $instance[ 'order' ] ) ? $instance[ 'order' ] : 'DESC';
		$category = isset( $instance[ 'category' ] ) ? $instance[ 'category' ] : 'any';
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'cuar' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'no_content_message' ); ?>"><?php _e( 'Message shown when no posts:', 'cuar' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'no_content_message' ); ?>" name="<?php echo $this->get_field_name( 'no_content_message' ); ?>" type="text" value="<?php echo esc_attr( $no_content_message ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'posts_per_page' ); ?>"><?php _e( 'Max. number of posts:', 'cuar' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'posts_per_page' ); ?>" name="<?php echo $this->get_field_name( 'posts_per_page' ); ?>" type="text" value="<?php echo esc_attr( $posts_per_page ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order By:', 'cuar' ); ?></label> 
			<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>" class="widefat">
				<option value="date" <?php selected( $orderby, 'date' ); ?>><?php _e('Date', 'cuar' ); ?></option>
				<option value="name" <?php selected( $orderby, 'name' ); ?>><?php _e('Name', 'cuar' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order:', 'cuar' ); ?></label> 
			<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" class="widefat">
				<option value="ASC" <?php selected( $order, 'ASC' ); ?>><?php _e('Ascending', 'cuar' ); ?></option>
				<option value="DESC" <?php selected( $order, 'DESC' ); ?>><?php _e('Descending', 'cuar' ); ?></option>
			</select>
		</p>
		
<?php 	if ( $this->get_associated_taxonomy()!=null ) : ?>		
		
		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category:', 'cuar' ); ?></label> 
			
<?php 		wp_dropdown_categories( array(
					'taxonomy' 			=> $this->get_associated_taxonomy(),
					'show_option_all' 	=> __( 'Any', 'cuar' ), 
					'orderby' 			=> 'name', 
					'hide_empty' 		=> 0, 
					'name' 				=> $this->get_field_name( 'category' ), 
					'id' 				=> $this->get_field_id( 'category' ),
					'selected' 			=> $category,
					'class' 			=> 'widefat',
					'hierarchical' 		=> 1,
					'hide_if_empty' 	=> false
				)); 
?>
		</p>
		
<?php 	endif; ?>
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
		
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['order'] = ( !empty( $new_instance['order'] ) ) ? $new_instance['order'] : 'DESC';
		$instance['orderby'] = ( !empty( $new_instance['orderby'] ) ) ? $new_instance['orderby'] : 'date';
		$instance['posts_per_page'] = ( !empty( $new_instance['posts_per_page'] ) ) ? (int)( $new_instance['posts_per_page'] ) : 5;
		$instance['category'] = ( !empty( $new_instance['category'] ) ) ? $new_instance['category'] : 'any';

		return $instance;
	}
}

endif; // if (!class_exists('CUAR_ContentListWidget')) 
