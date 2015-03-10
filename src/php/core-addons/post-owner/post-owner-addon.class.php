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

require_once( CUAR_INCLUDES_DIR . '/core-classes/addon.class.php' );

if (!class_exists('CUAR_PostOwnerAddOn')) :

/**
 * Add-on to provide all the stuff required to set an owner on a post type and include that post type in the 
 * customer area.
 *
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PostOwnerAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'post-owner', '4.0.0' );
	}
	
	public function get_addon_name() {
		return __( 'Post Owner', 'cuar' );
	}

	public function run_addon( $plugin ) {
		// Init the admin interface if needed
		if ( is_admin() ) {
			add_action( 'cuar/core/on-plugin-update', array( &$this, 'plugin_version_upgrade' ), 10, 2 );

			add_action('cuar/core/addons/after-init', array( &$this, 'customize_post_edit_pages'));
			add_action('cuar/core/addons/after-init', array( &$this, 'customize_post_list_pages'));

			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		} else {
			add_action( 'template_redirect', array( &$this, 'protect_single_post_access' ) );
		}
			
		add_action('cuar/core/ownership/printable-owners?owner-type=usr', array( &$this, 'get_printable_owners_for_type_usr'), 10 );		
	}	
	
	/*------- QUERY FUNCTIONS ---------------------------------------------------------------------------------------*/
	
	/**
	 * Builds the meta query to check if a user owns a post 
	 * @param int $user_id The user ID of the owner
	 * @return array See the meta query documentation on WP codex
	 */
	public function get_meta_query_post_owned_by( $user_id ) {
		$user_id = apply_filters( 'cuar/core/ownership/content/meta-query/override-owner-id', $user_id );
		
		$base_meta_query = array(
				'relation' => 'OR',
				$this->get_owner_meta_query_component( 'usr', $user_id )
			);
		
		return apply_filters( 'cuar/core/ownership/content/meta-query', $base_meta_query, $user_id );
	}
	
	public function get_owner_meta_query_component( $owner_type, $owner_id ) {
		return array(
				'key' 		=> self::$META_OWNER_QUERYABLE,
				'value' 	=> '|' . $owner_type . '_' . $owner_id . '|',
				'compare' 	=> 'LIKE'
			);
	}

	/*------- PRIVATE FILE STORAGE DIRECTORIES ----------------------------------------------------------------------*/

	/**
	 * This is the base directory where we will store the user files
	 *
	 * @return string
	 */
	public function get_base_private_storage_directory( $create_dirs = false ) {
		$dir = WP_CONTENT_DIR . '/customer-area';	
		$dir = apply_filters( 'cuar/core/ownership/base-private-storage-directory', $dir );
		if ( $create_dirs && !file_exists( $dir ) ) mkdir( $dir, 0775, true );	
		return $dir;
	}
	
	/**
	 * This is the base URL where we can access the user files directly (should be protected to forbid direct
	 * downloads)
	 *
	 * @return string
	 */
	public function get_base_private_storage_url() {
		return WP_CONTENT_URL . '/customer-area';
	}
	
	/**
	 * Get the absolute path to a private file.
	 *
	 * @param int $post_id
	 * @param string $filename
	 * @param boolean $create_dirs
	 * @return boolean|string
	 */
	public function get_private_file_path( $filename, $post_id, $create_dirs = false ) {
		if ( empty( $post_id ) || empty( $filename ) ) return false;
	
		$dir = $this->get_base_private_storage_directory() . '/' . $this->get_private_storage_directory( $post_id );
		if ( $create_dirs && !file_exists( $dir ) ) mkdir( $dir, 0775, true );
	
		return $dir . '/' . $filename;
	}
	
	/**
	 * Get the absolute path to a private file.
	 *
	 * @param int $post_id
	 * @param string $filename
	 * @param boolean $create_dirs
	 * @return boolean|string
	 */
	public function get_owner_file_path( $filename, $owner_ids, $owner_type, $create_dirs = false ) {
		if ( empty( $owner_ids ) || empty( $owner_type ) || empty( $filename ) ) return false;
	
		$dir = $this->get_base_private_storage_directory() . '/' . $this->get_owner_storage_directory( $owner_ids, $owner_type );
		if ( $create_dirs && !file_exists( $dir ) ) mkdir( $dir, 0775, true );
	
		return $dir . '/' . $filename;
	}
	
	/**
	 * Get a user's private storage directory. This directory is relative to the main upload directory
	 *
	 * @param int $user_id The id of the user, or null to get the base directory
	 */
	public function get_private_storage_directory( $post_id, $absolute = false, $create_dirs = false ) {
		if ( empty( $post_id ) ) return false;
	
		$owner_ids = $this->get_post_owner_ids( $post_id );
		$owner_type = $this->get_post_owner_type( $post_id );		
		
		return $this->get_owner_storage_directory($owner_ids, $owner_type, $absolute, $create_dirs );
	}
	
	/**
	 * Get a user's private storage directory. This directory is relative to the main upload directory
	 *
	 * @param int $user_id The id of the user, or null to get the base directory
	 */
	public function get_owner_storage_directory( $owner_ids, $owner_type, $absolute = false, $create_dirs = false ) {
		if ( empty( $owner_ids ) || empty( $owner_type ) ) return false;

		if ( is_array( $owner_ids ) ) {
			$dir = md5( $owner_type . '_' . implode( ',', $owner_ids ) );
		} else {		
			$dir = md5( $owner_type . '_' . $owner_ids );
		}
	
		if ( $absolute ) $dir = $this->get_base_private_storage_directory() . "/" . $dir;
		if ( $create_dirs && !file_exists( $dir ) ) mkdir( $dir, 0775, true );
	
		return $dir;
	}
	
	/*------- ACCESS TO OWNER INFO ----------------------------------------------------------------------------------*/
	
	/** Check if the given value is a valid owner type */
	public function is_valid_owner_type($type) {
		$types = $this->get_owner_types();
		return array_key_exists($type, $types);
	}

	
	/**
	 * Returns all the possible owner types in the form of an associative array. The key is the owner type (should
	 * remain constant) and the value is a string to be displayed in various places (should be internationalised). 
	 * 
	 * @return mixed
	 */
	public function get_owner_types() {
		if ($this->owner_types==null) {
			$this->owner_types = apply_filters('cuar/core/ownership/owner-types', array( 'usr' => __('User', 'cuar') ) );
		}
		return $this->owner_types;
	}

	
	/**
	 * Check if a user is an owner of the given post. 
	 * 
	 * @param int $post_id
	 * @param int $user_id
	 */
	public function is_user_owner_of_post( $post_id, $user_id ) {
		$result = false;
		
		// We take care of the single user ownership
		$owner_type = $this->get_post_owner_type( $post_id );
		$owner_ids = $this->get_post_owner_ids( $post_id );
		
		if ( $owner_type=='usr' ) {
			$result = in_array( $user_id, $owner_ids );
		} else {
			$result = false;
		}

		return apply_filters( 'cuar/core/ownership/validate-post-ownership', $result, $post_id, $user_id, $owner_type, $owner_ids );
	}
	
	/**
	 * Get the owner id of the post
	 *
	 * @param int $post_id The post ID
	 * @return int 0 if no owner is set
	 */
	public function get_post_owner_ids( $post_id ) {
		$owner_ids = get_post_meta( $post_id, self::$META_OWNER_IDS, true );
		if ( !$owner_ids || empty( $owner_ids ) ) $owner_ids = array();
		if ( !is_array( $owner_ids ) ) $owner_ids = array( $owner_ids );
		return $owner_ids;
	}

	/**
	 * Get the owner type of the post (user, role, ...)
	 *
	 * @param int $post_id The post ID
	 * @return string the type of ownership (defaults to 'usr')
	 */
	public function get_post_owner_type( $post_id ) {
		$owner_type = get_post_meta( $post_id, self::$META_OWNER_TYPE, true );
		if ( !$owner_type || empty( $owner_type ) ) $owner_type = 'usr';
		return $owner_type;
	}

	/**
	 * Get the name to be displayed 
	 *
	 * @param int $post_id The post ID
	 * @return string the type of ownership (defaults to 'usr')
	 */
	public function get_post_owner_displayname( $post_id, $prefix_with_type=false ) {
		if ($prefix_with_type) {
			$name = get_post_meta( $post_id, self::$META_OWNER_SORTABLE_DISPLAYNAME, true );
			if ( !$name || empty( $name ) ) $name = __( 'Unknown', 'cuar' );
			return apply_filters( 'cuar/core/ownership/sortable-displayname', $name, $post_id );
		} else {
			$name = get_post_meta( $post_id, self::$META_OWNER_DISPLAYNAME, true );
			if ( !$name || empty( $name ) ) $name = __( 'Unknown', 'cuar' );
			return apply_filters( 'cuar/core/ownership/displayname', $name, $post_id );
		} 
	}
	
	/**
	 * Get the owner details (id and type) from post metadata
	 *
	 * @return NULL|array associative array with keys 'ids' and 'type'
	 */
	public function get_post_owner( $post_id ) {
		$owner = array(
				'ids' 	=> $this->get_post_owner_ids( $post_id ),
				'type'	=> $this->get_post_owner_type( $post_id )
			);

		if ( !is_array( $owner['ids'] ) ) $owner['ids'] = array( $owner['ids'] );		
		return $owner;
	}

	/**
	 * Get the real user ids behind the logical owner of the post
	 *
	 * @return array User ids 
	 */
	public function get_post_owner_user_ids( $post_id ) {
		$owner_ids = $this->get_post_owner_ids( $post_id );
		$owner_type = $this->get_post_owner_type( $post_id );
		
		// If the owner is already a user, no worries
		if ( $owner_type=='usr' ) {
			return $owner_ids;
		}
		
		// Let other add-ons return what they want
		return apply_filters( 'cuar/core/ownership/real-user-ids?owner-type=' . $owner_type, array(), $owner_ids );
	}
	
	/**
	 * Save the owner details for the given post
	 * 
	 * @param int $post_id
	 * @param string $owner_id
	 * @param string $owner_type
	 */
	public function save_post_owners( $post_id, $owner_ids, $owner_type, $ensure_type_exists = true ) {
		// Check owner type exists
		$owner_types = $this->get_owner_types();		
		if ( $ensure_type_exists && !array_key_exists( $owner_type, $owner_types ) ) {
			$this->plugin->add_admin_notice( 'Invalid owner type, some add-on must be doing something wrong' );
			return;
		}
		
		// Serialize the owner ids for queries 
		$queryable_ids = $this->encode_queryable_owner_ids( $owner_ids, $owner_type );
		
		// Some defaults for the owner type 'usr' 
		$displayname = '?';
		if ($owner_type=='usr') {
			$names = array();
			foreach ( $owner_ids as $id ) {
				$u = new WP_User( $id );
				$names[] = $u->display_name;
			}
			asort( $names );
			$displayname = implode( ', ', $names );
		} 
		$displayname = apply_filters( 'cuar/core/ownership/saved-displayname', $displayname,
				$post_id, $owner_ids, $owner_type );
		
		$sortable_displayname = $owner_types[ $owner_type ] . ' - ' . $displayname;
		$sortable_displayname = apply_filters( 'cuar/core/ownership/saved-sortable-displayname', $sortable_displayname,
				$post_id, $owner_ids, $owner_type, $displayname );
		
		// Persist data
		update_post_meta( $post_id, self::$META_OWNER_IDS, $owner_ids );
		update_post_meta( $post_id, self::$META_OWNER_TYPE, $owner_type );
		update_post_meta( $post_id, self::$META_OWNER_QUERYABLE, $queryable_ids );	
		update_post_meta( $post_id, self::$META_OWNER_DISPLAYNAME, $displayname );
		update_post_meta( $post_id, self::$META_OWNER_SORTABLE_DISPLAYNAME, $sortable_displayname );
	}
	
	/**
	 * Encode an array of users/user groups for storage in the meta table. We expect a dictionnary where the keys are 
	 * user groups and values are arrays of user IDs.
	 */
	private static function encode_queryable_owner_ids( $user_ids, $owner_type ) {	
		$sep = '|' . $owner_type . '_';
		$raw = $sep . implode( $sep, array_filter( $user_ids ) ) . '|';
		return $raw;
	}

	/** @var array $owner_types */
	private $owner_types = null;
	
	/*------- CUSTOMISATION OF THE LISTING OF POSTS -----------------------------------------------------------------*/
	
	public function customize_post_list_pages() {
		$post_types = $this->plugin->get_content_post_types();
		foreach ($post_types as $type) {
			add_filter( "manage_edit-{$type}_columns", array( &$this, 'owner_column_register' ));
			add_action( "manage_{$type}_posts_custom_column", array( &$this, 'owner_column_display'), 10, 2 );
			add_filter( "manage_edit-{$type}_sortable_columns", array( &$this, 'owner_column_register_sortable' ));
		}
		add_filter( 'request', array( &$this, 'owner_column_orderby' ));				
	}
	
	/**
	 * Register the owner column
	 */
	public function owner_column_register( $columns ) {
		$columns['cuar_owner'] = __( 'Owner', 'cuar' );
		return $columns;
	}
	
	/**
	 * Display the column content
	 */
	public function owner_column_display( $column_name, $post_id ) {
		if ( 'cuar_owner' != $column_name )
			return;
		
		$txt = apply_filters( 'cuar/core/ownership/owner-column-text', null, $post_id );
		echo $txt!=null ? $txt : $this->get_post_owner_displayname( $post_id, true );
	}
	
	/**
	 * Register the column as sortable
	 */
	public function owner_column_register_sortable( $columns ) {
		$columns['cuar_owner'] = 'cuar_owner';	
		return $columns;
	}
	
	/**
	 * Handle sorting of data
	 */
	public function owner_column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'cuar_owner' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
					'meta_key' 	=> self::$META_OWNER_SORTABLE_DISPLAYNAME,
					'orderby' 	=> 'meta_value'
				) );
		}
	
		return $vars;
	}
	
	/*------- CUSTOMISATION OF THE EDIT PAGE FOR A POST WITH OWNER INFO ---------------------------------------------*/

	/**
	 * Enqueues the select script on the user-edit and profile screens.
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		$post_types = $this->plugin->get_content_post_types();		
	
		if ( isset( $screen->id ) ) {
			if ( in_array( $screen->id, $post_types ) ) {
				$this->plugin->enable_library('jquery.select2');
			}
		}
	}
	
	public function customize_post_edit_pages() {
		add_action( 'admin_menu', array( &$this, 'register_post_edit_meta_boxes' ));
		add_action( 'save_post', array( &$this, 'do_save_post' ), 10, 2 );
	}
	
	/**
	 * Register some additional boxes on the page to edit the files
	 */
	public function register_post_edit_meta_boxes() {
		$post_types = $this->plugin->get_content_post_types();		
		foreach ($post_types as $type) {
			add_meta_box( 
					'cuar_post_owner', 
					__( 'Assignment', 'cuar' ), 
					array( &$this, 'print_owner_meta_box'), 
					$type, 
					'normal', 
					'high'
				);
		}
	}

	/**
	 * Print the metabox to select the owner of the file
	 */
	public function print_owner_meta_box() {
		global $post;
		wp_nonce_field( plugin_basename(__FILE__), 'wp_cuar_nonce_owner' );
	
		$current_owner_ids = $this->get_post_owner_ids( $post->ID );
		$current_owner_type = $this->get_post_owner_type( $post->ID );		
		
		do_action( "cuar/core/ownership/before-owner-meta-box" );

		// $owner_type_field_id = 'cuar_owner_type', $owner_field_id = 'cuar_owner'

		echo '<table class="metabox-row">';
		echo '<tr>';
		echo '<td class="label"><label for="cuar_owner_type">' . __('Select the owner', 'cuar') . '</label></td>';
		echo '<td class="field">';		
		$this->print_owner_type_select_field( 'cuar_owner_type', null, $current_owner_type );
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td class="label"></td>';
		echo '<td class="field">';				
		$this->print_owner_select_field( 'cuar_owner_type', 'cuar_owner', null, $current_owner_type, $current_owner_ids );
		echo '</td>';
		echo '</tr>';
		echo '</table>';

		$this->print_owner_select_javascript( 'cuar_owner_type', 'cuar_owner' );
		
		do_action( "cuar/core/ownership/after-owner-meta-box" );
	}
	
	/**
	 * Print a select field with all users
	 * @param unknown $field_id
	 * @param unknown $current_owner_type
	 * @param unknown $current_owner_id
	 */
	public function get_printable_owners_for_type_usr( $in ) {
		$all_users = apply_filters( 'cuar/core/ownership/selectable-owners?owner-type=usr', null );		
		if ( null===$all_users ) {
			$all_users = get_users( array( 'orderby' => 'display_name', 'fields' => 'all_with_meta' ) );
		}	
		
		$out = $in;
		
		foreach ( $all_users as $u ) {
			$out[ $u->ID ] = $u->display_name;
		}
		
		return $out;
	}

	/**
	 * Print the metabox to select an owner type
	 */
	public function print_owner_type_select_field( $owner_type_field_id, $field_group=null, $selected_owner_type='usr' ) {
		if ($field_group!=null) {
			$owner_type_field_name = $field_group . '[' . $owner_type_field_id . ']';
		} else {
			$owner_type_field_name = $owner_type_field_id;
		}

		$owner_types = apply_filters( 'cuar/core/ownership/selectable-owner-types', null );
		if ( null===$owner_types ) {
			$owner_types = $this->get_owner_types();
		}
		
		if (count($owner_types)==1) {
			reset($owner_types);
			echo current($owner_types); 
?>
		<input type="hidden" name="<?php echo $owner_type_field_name; ?>" id="<?php echo $owner_type_field_id; ?>" value="<?php echo key($owner_types); ?>" />
<?php
		} else {
?>
			<select name="<?php echo $owner_type_field_name; ?>" id="<?php echo $owner_type_field_id; ?>" class="form-control" >
<?php 		foreach ( $owner_types as $type_id => $type_label ) : 
				$selected =  ( $selected_owner_type!=$type_id ? '' : ' selected="selected"' ); 
?>
				<option value="<?php echo $type_id;?>"<?php echo $selected; ?>><?php echo $type_label; ?></option>
<?php 		endforeach; ?>				
			</select>
<?php 						
		}
	}
	
	/**
	 * Print the javascript to change the owner select according to the owner type select
	 */
	public function print_owner_select_javascript($owner_type_field_id, $owner_field_id) {
?>
		<script type="text/javascript">
		<!--
			jQuery( document ).ready( function($) {
				$( '#<?php echo $owner_type_field_id; ?>' ).change(function() {
					var type = $(this).val();
					var newVisibleId = '#<?php echo $owner_field_id ?>_' + type + '_container';

					// Do nothing if already visible
					if ( $(newVisibleId).is(":visible") ) return

					// Hide previous and then show new
					if ( $('.<?php echo $owner_type_field_id; ?>_owner_select_container:visible').length<=0 ) {
						$(newVisibleId).fadeToggle();
					} else {
						$('.<?php echo $owner_type_field_id; ?>_owner_select_container:visible').fadeToggle("fast", function () {
							$(newVisibleId).fadeToggle();
						});
					}
				});
			});
		//-->
		</script>
<?php 
	}
	
	/**
	 * Print the metabox to select an owner
	 */
	public function print_owner_select_field($owner_type_field_id, $owner_field_id, $field_group=null, $selected_owner_type='usr', $selected_owner_ids=array()) {
		global $post;

		$hide_if_single_owner = $this->plugin->get_option( CUAR_Settings::$OPTION_HIDE_SINGLE_OWNER_SELECT );
		
		$owner_types = apply_filters( 'cuar/core/ownership/selectable-owner-types', null );
		if ( null===$owner_types ) {
			$owner_types = $this->get_owner_types();
		}
		
		if ( array_key_exists( $selected_owner_type, $owner_types ) ) {
			$visible_owner_select = $selected_owner_type;
		} else {
			reset( $owner_types );
			$visible_owner_select = key( $owner_types ); 
		}
		
		// Don't hide if we can select
		if ( count( $owner_types )!=1 ) {
			$hide_if_single_owner = false;
		}
		
		foreach ( $owner_types as $type_id => $type_label ) { 		
			$container_id = $owner_field_id . '_' . $type_id . '_container';	
			$field_id = $owner_field_id . '_' . $type_id . '_id'; 					
			if ($field_group!=null) {
				$field_name = $field_group . '[' . $field_id . ']';
			} else {
				$field_name = $field_id;
			}
			
			$owners = apply_filters( 'cuar/core/ownership/printable-owners?owner-type=' . $type_id, array() );

			$hidden = ( $visible_owner_select==$type_id ? '' : ' style="display: none;"' );

			printf( '<div id="%s" class="field %s" %s>', 
					$container_id,
					$owner_type_field_id . '_owner_select_container',
					$hidden
				);
			
			if ( count( $owners )==0 )	{
				printf( '<span class="no-owner %s"><em>%s</em></span>', 
						$owner_type_field_id . '_owner_select', 
						__( 'It seems you cannot select any owner of that type.', 'cuar' ) );
			} else if ( count( $owners )==1 )	{
				foreach ( $owners as $id => $name ) {
					printf( '<input type="hidden" name="%s" value="%s" />', $field_name, $id );
										
					if ( $hide_if_single_owner ) {
						printf( '<span id="%s" class="single-owner hidden-message %s"><em>%s</em></span>', 
								$field_id, 
								$owner_type_field_id . '_owner_select', 
								apply_filters( 'cuar/core/ownership/hidden-single-selectable-owner-text', __( 'The owner is hidden', 'cuar' ) ) );
					} else {
						printf( '<span id="%s" class="single-owner %s"><em>%s</em></span>', 
								$field_id, 
								$owner_type_field_id . '_owner_select', 
								apply_filters( 'cuar/core/ownership/single-selectable-owner-text', $name ) );
					}
				}
			} else {	
				$enable_multiple_selection = apply_filters( 'cuar/core/ownership/enable-multiple-select?owner-type=' . $type_id, false );
				$multiple = $enable_multiple_selection ? ' multiple="multiple" size="8"' : '';
				
				printf( '<select id="%s" name="%s" class="%s" %s data-placeholder="%s" class="form-control">',
						$field_id,
						$enable_multiple_selection ? $field_name . '[]' : $field_name,
						$owner_type_field_id . '_owner_select',
						$multiple,
						__('Select or search an owner', 'cuar')
					);
	
				foreach ( $owners as $id => $name ) {
					$selected =  ( $selected_owner_type==$type_id && in_array( $id, $selected_owner_ids ) ) ? ' selected="selected"' : '';					
					printf('<option value="%1$s" %2$s>%3$s</option>', $id, $selected, $name );
				}
				
				echo '</select>';
				
				$theme_support = get_theme_support( 'customer-area.library.jquery.select2' );
				if ( is_admin() 
						|| $theme_support===false 
						|| ( is_array( $theme_support ) && !in_array( 'markup', $theme_support[0] ) ) ) {				
					$this->plugin->enable_library( 'jquery.select2' );
?>
					<script type="text/javascript">
						<!--
						jQuery("document").ready(function($){
							$("#<?php echo $field_id; ?>").select2({
								width:						"100%"
							});
						});
						//-->
					</script>
<?php 			
				}
			}
			
			echo '</div>';
		}			
	}
	
	/**
	 * Callback to handle saving a post
	 *  
	 * @param int $post_id
	 * @param string $post
	 * @return void|unknown
	 */
	public function do_save_post( $post_id, $post = null ) {
		global $post;
		
		// When auto-saving, we don't do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
	
		// Only take care of private post types
		$private_post_types = $this->plugin->get_content_post_types();	
		if ( !$post || !in_array( get_post_type( $post->ID ), $private_post_types ) ) return;
		
		// Save the owner details
		if ( !wp_verify_nonce( $_POST['wp_cuar_nonce_owner'], plugin_basename(__FILE__) ) ) return $post_id;
		
		$previous_owner = $this->get_post_owner( $post_id );
		$new_owner = $this->get_owner_from_post_data();
	
		// Other addons can do something before we save
		do_action( "cuar/core/ownership/before-save-owner", $post_id, $previous_owner, $new_owner );
		
		// Save owner details
		$this->save_post_owners( $post_id, $new_owner['ids'], $new_owner['type'] );
		
		// Other addons can do something after we save
		do_action( "cuar/core/ownership/after-save-owner", $post_id, $post, $previous_owner, $new_owner );

		return $post_id;
	}
	
	/**
	 * Get the owner details (id and type) from HTTP POST data
	 * 
	 * @return NULL|array associative array with keys 'ids' and 'type'
	 */
	public function get_owner_from_post_data( $owner_type_field_id = 'cuar_owner_type', $owner_field_id = 'cuar_owner' ) {
		if ( !isset($_POST[ $owner_type_field_id ]) 
				|| !isset($_POST[ $owner_field_id. '_' . $_POST[ $owner_type_field_id ] . '_id']) ) {
			return null;
		}
		
		$owner = array(
				'ids' 	=> $_POST[ $owner_field_id . '_' . $_POST[ $owner_type_field_id ] . '_id'],
				'type'	=> $_POST[ $owner_type_field_id ]
			);

		if ( !is_array( $owner['ids'] ) ) $owner['ids'] = array( $owner['ids'] );		
		return $owner;
	}

	/*------- FRONTEND ----------------------------------------------------------------------------------------------*/

	/**
	 * Protect access to single posts: only for author and owner.
	 */
	public function protect_single_post_access() {
		$private_post_types = $this->plugin->get_content_post_types();
			
		// If not on a matching post type, we do nothing
		if ( empty( $private_post_types ) ||!is_singular( $private_post_types ) ) return;

		// If not logged-in, we ask for details
		if ( !is_user_logged_in() ) {
			$this->plugin->login_then_redirect_to_url( get_permalink() );
		}
	
		// If not authorized to view the page, we bail
		$post = get_queried_object();
		$author_id = $post->post_author;	
		$current_user_id = apply_filters( 'cuar/core/ownership/protect-single-post/override-user-id', get_current_user_id() );
	
		$is_current_user_owner = $this->is_user_owner_of_post( $post->ID, $current_user_id );
		if ( !( $is_current_user_owner || $author_id==$current_user_id || current_user_can('cuar_view_any_' . get_post_type()) )) {
			wp_die( __( "You are not authorized to view this page", "cuar" ) );
			exit();
		}
		
		do_action( 'cuar/core/ownership/protect-single-post/on-access-granted', $post );
	}
	
	/*------- OTHER FUNCTIONS ---------------------------------------------------------------------------------------*/
	
	/**
	 * When the plugin is upgraded
	 * 
	 * @param unknown $from_version
	 * @param unknown $to_version
	 */
	public function plugin_version_upgrade( $from_version, $to_version ) {
		// If upgrading from before 2.0.0 we must update the post meta fields 
		if ( $from_version<'2.0.0' ) {
			global $wpdb;
			
			// Find all existing owner ids
			$owner_metas = $wpdb->get_results( $wpdb->prepare(
				  		  "SELECT meta_id, post_id, meta_key, meta_value "
						. "FROM $wpdb->postmeta "
						. "WHERE meta_key = %s", 
					'cuar_owner') );
			
			foreach ($owner_metas as $m) {	
				// Before 2.0.0 there was no owner type, so default to 'user'	
				$owner_type = 'user';	
				$owner_id = $m->meta_value;
				
				// Add post meta (owner type, display names)
				$u = new WP_User($owner_id);
				$display_name = $u->display_name;
				$sortable_display_name = sprintf( __('User - %s', 'cuar'), $u->display_name);

				update_post_meta($m->post_id, self::$META_OWNER_TYPE, $owner_type );
				update_post_meta($m->post_id, self::$META_OWNER_DISPLAYNAME, $display_name);	
				update_post_meta($m->post_id, self::$META_OWNER_SORTABLE_DISPLAYNAME, $sortable_display_name);
				update_post_meta($m->post_id, self::$META_OWNER_QUERYABLE, $owner_type . '_' . $owner_id );	
				
				// If owner had a directory, rename that directory into the new naming scheme
				$base_storage_directory = $this->get_base_private_storage_directory(true);
				$new_name = $this->get_owner_storage_directory($owner_id, $owner_type);
				$old_name = get_user_meta($owner_id, 'cuar_directory', true);
				
				if (!empty($old_name)) {
					if (file_exists($base_storage_directory . "/" . $old_name)) {
						rename( $base_storage_directory . "/" . $old_name,  $base_storage_directory . "/" . $new_name );
					}
					delete_user_meta($owner_id, 'cuar_directory');
				}
			}
		}
		
		if ( $from_version<'2.3.0' ) {
			// Migrate all previous owner formats to the new one
			$posts_with_owner = get_posts( array( 
					'numberposts' 	=> -1, 
					'post_status'	=> array( 'publish', 'auto-draft', 'future', 'draft', 'pending', 'private', 'inherit', 'trash' ),
					'post_type' 	=> array( 'cuar_private_page', 'cuar_private_file' ) 
				));

			foreach ( $posts_with_owner as $p ) {
				$old_type = get_post_meta( $p->ID, 'cuar_owner_type',  true );
				$old_owner = get_post_meta( $p->ID, 'cuar_owner',  true );
				$old_path = $this->get_owner_storage_directory( $old_owner, $old_type, true, false );
				
				$new_type = null;
				$new_owners = array( $old_owner );
				
				switch ( $old_type ) {
					case 'user':
						$new_type = 'usr';
						break;
					case 'user_group':
						$new_type = 'grp';
						break;
					case 'role':
						$new_type = 'rol';
						break;
				}
				
				if ( $new_type!=null ) {
					$this->save_post_owners( $p->ID, $new_owners, $new_type, false );
					delete_post_meta( $p->ID, 'cuar_owner' );

					$new_path = $this->get_owner_storage_directory( $new_owners, $new_type, true, false );
					if ( file_exists( $old_path ) )	{
						rename( $old_path, $new_path );
					}
				}
			}
		}
	}

	public static $META_OWNER_QUERYABLE				= 'cuar_owner_queryable';
	public static $META_OWNER_IDS 					= 'cuar_owners';
	public static $META_OWNER_TYPE 					= 'cuar_owner_type';
	public static $META_OWNER_DISPLAYNAME 			= 'cuar_owner_displayname';
	public static $META_OWNER_SORTABLE_DISPLAYNAME 	= 'cuar_owner_sortable_displayname';
	
}

// Make sure the addon is loaded
new CUAR_PostOwnerAddOn();

endif; // if (!class_exists('CUAR_PrivateFileAddOn')) 
