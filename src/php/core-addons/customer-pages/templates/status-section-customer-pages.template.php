<?php
/** Template version: 2.0.0
 *
 * -= 2.0.0 =-
 * - Add cuar- prefix to bootstrap classes
 *
 * -= 1.0.0 =-
 * - Initial version
 *
 */
?>

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

<table class="widefat cuar-status-table">
	<thead>
		<tr>
			<th><?php _e( 'Page', 'cuar' ); ?></th>
			<th><?php _e( 'Slug', 'cuar' ); ?></th>
			<th><?php _e( 'Order', 'cuar' ); ?></th>
			<th><?php _e( 'ID', 'cuar' ); ?></th>
			<th><?php _e( 'Sidebar', 'cuar' ); ?></th>
			<th><?php _e( 'Type', 'cuar' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
	$cp_addon = $this->plugin->get_addon('customer-pages');
	$customer_area_pages = $cp_addon->get_customer_area_pages();
	
	foreach ( $customer_area_pages as $slug => $page ) :
		$page_id = $page->get_page_id();
		$tr_class = $page_id<=0 ? 'cuar-needs-attention' : '';
?>
		<tr class="<?php echo $tr_class; ?>">
			<td><?php echo $page->get_title(); ?></td>
			<td><?php echo $page->get_slug(); ?></td>
			<td><?php echo $page->get_priority(); ?></td>
			<td><?php echo $page_id>0 ? $page_id : '?'; ?></td>
			<td><?php echo $page->has_page_sidebar() ? 'Yes' : ''; ?></td>
			<td><?php echo $page->get_type(); ?></td>
			<td>
<?php 			if ( $page_id>0 ) {
					printf( '<a href="%1$s">%2$s</a>', admin_url('post.php?action=edit&post=' . $page_id), __('Edit', 'cuar') );
					echo ' | ';
					printf( '<a href="%1$s">%2$s</a>', get_permalink( $page_id ), __('View', 'cuar') );
				}
?>
			</td>
		</tr>	
<?php
	endforeach;
?>
	</tbody>
</table>

<?php 
	$orphan_pages = $cp_addon->get_potential_orphan_pages();
	
	if ( !empty( $orphan_pages ) ) {
?>

<h2><?php _e( 'Potential orphan pages', 'cuar' ); ?></h2>

<div class="cuar-needs-attention">	
	<p>
		<?php _e( 'Some pages in your site seem to contain Customer Area shortcodes but are not registered in the Customer Area pages settings. Those are probably pages you had created and ' 
				. 'trashed when originally set up the plugin. You should delete them for good either manually or by letting us do it for you.', 'cuar' ); ?>
	</p>
		 
	<p class="cuar-suggested-action"><span class="cuar-text"><?php _e('Suggested action', 'cuar' ); ?></span>
		<input type="submit" id="cuar-remove-orphan-pages" name="cuar-remove-orphan-pages" class="button button-primary" value="<?php esc_attr_e( 'Delete orphan pages', 'cuar' ); ?> &raquo;" />
		<?php wp_nonce_field( 'cuar-remove-orphan-pages', 'cuar-remove-orphan-pages_nonce' ); ?>
		
		<script type="text/javascript">
		<!--
			jQuery(document).ready(function($) {
				$('#cuar-remove-orphan-pages').click('click', function(){
					var answer = confirm( "<?php echo str_replace( '"', '\\"', __('Are you sure that you want to delete those pages (this operation cannot be undone)?', 'cuar') ); ?>" );
					return answer;
				});
			});
		//-->
		</script>
	</p>	
</div>

<table class="widefat cuar-status-table">
	<thead>
		<tr>
			<th><?php _e( 'Title', 'cuar' ); ?></th>
			<th><?php _e( 'ID', 'cuar' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
	$cp_addon = $this->plugin->get_addon('customer-pages');
	$customer_area_pages = $cp_addon->get_customer_area_pages();
	
	foreach ( $orphan_pages as $o ) :
?>
		<tr class="<?php echo $tr_class; ?>">
			<td><?php echo $o->post_title; ?></td>
			<td><?php echo $o->ID; ?></td>
			<td>
<?php 			if ( $page_id>0 ) {
					printf( '<a href="%1$s" class="cuar-button">%2$s</a>', admin_url('post.php?action=edit&post=' . $o->ID), __('Edit', 'cuar') );
					echo ' | ';
					printf( '<a href="%1$s" class="cuar-button">%2$s</a>', get_permalink( $o->ID ), __('View', 'cuar') );
				}
?>
			</td>
		</tr>	
<?php
	endforeach;
?>
	</tbody>
</table>



<?php 
	}
?>