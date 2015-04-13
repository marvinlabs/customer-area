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

if (!class_exists('CUAR_PrivateFileAdminInterface')) :

/**
 * Administation area for private files
 * 
 * @author Vincent Prat @ MarvinLabs
 */
class CUAR_PrivateFileAdminInterface {
	
	public function __construct( $plugin, $private_file_addon ) {
		$this->plugin = $plugin;
		$this->private_file_addon = $private_file_addon;

		// Settings
		add_filter( 'cuar/core/settings/settings-tabs', array( &$this, 'add_settings_tab' ), 520, 1 );
		add_action( 'cuar/core/settings/print-settings?tab=cuar_private_files', array( &$this, 'print_settings' ), 10, 2 );
		add_filter( 'cuar/core/settings/validate-settings?tab=cuar_private_files', array( &$this, 'validate_options' ), 10, 3 );
		
		if ( $this->private_file_addon->is_enabled() ) {
			// File edit page
			add_action( 'admin_menu', array( &$this, 'register_edit_page_meta_boxes' ) );
			add_action( 'cuar/core/ownership/after-save-owner', array( &$this, 'do_save_post' ), 10, 4 );				
			add_action( 'post_edit_form_tag' , array( &$this, 'post_edit_form_tag' ) );
		}		
	}

	/*------- CUSTOMISATION OF THE EDIT PAGE OF A PRIVATE FILES ------------------------------------------------------*/

	/**
	 * Alter the edit form tag to say we have files to upload
	 */
	public function post_edit_form_tag() {
		global $post;
		if ( !$post || get_post_type($post->ID)!='cuar_private_file' ) return;
		echo ' enctype="multipart/form-data" autocomplete="off"';
	}
	
	/**
	 * Register some additional boxes on the page to edit the files
	 */
	public function register_edit_page_meta_boxes() {
		add_meta_box( 
				'cuar_private_file_upload', 
				__('File', 'cuar'), 
				array( &$this, 'print_upload_meta_box'), 
				'cuar_private_file', 
				'normal', 'high');
	}

	/**
	 * Print the metabox to upload a file
	 */
	public function print_upload_meta_box() {
		global $post;
		wp_nonce_field( plugin_basename(__FILE__), 'wp_cuar_nonce_file' );
	
		$current_file = get_post_meta( $post->ID, 'cuar_private_file_file', true );

		do_action( "cuar/private-content/files/before-upload-meta-box" );
?>
		
<?php	if ( !empty( $current_file ) && isset( $current_file['file'] ) ) : ?>
		<div id="cuar-current-file" class="metabox-row">
			<h4><?php _e('File currently associated to this post', 'cuar');?></h4>
			<p><?php _e('Current file:', 'cuar');?> 
				<a href="<?php cuar_the_file_link( $post->ID, 'download' ); ?>" target="_blank">
					<?php echo basename($current_file['file']); ?></a>
			</p>
		</div>		
<?php 	endif; ?> 
	
<?php 
		$ftp_dir = trailingslashit( $this->private_file_addon->get_ftp_path() );
?>
		<div>
			<hr>
			<h4><?php _e('Change the associated file', 'cuar');?></h4>
			<span class="label"><label for="cuar_file_select_method"><?php _e('How to add the file?', 'cuar');?></label></span> 	
			<span class="field">
				<select id="cuar_file_select_method" name="cuar_file_select_method">
					<option value="cuar_direct_upload"><?php _e( 'Direct upload', 'cuar' ); ?>&nbsp;&nbsp;</option>
					<option value="cuar_from_ftp_folder"><?php _e( 'Copy from FTP folder', 'cuar' ); ?>&nbsp;&nbsp;</option>
				</select>
			</span>
		</div>

		<div id="cuar_direct_upload" class="metabox-row file-select-method">
			<span class="label"><label for="cuar_private_file_file"><?php _e('Pick a file', 'cuar');?></label></span> 	
			<span class="field"><input type="file" name="cuar_private_file_file" id="cuar_private_file_file" /></span>
		</div>	
			
<?php 
		if ( file_exists( $ftp_dir ) ) {
			$ftp_files = scandir( $ftp_dir );
		}
?>
		<div id="cuar_from_ftp_folder" class="metabox-row file-select-method" style="display: none;">
			<span class="label"><label for="cuar_selected_ftp_file"><?php _e('Pick a file', 'cuar');?></label></span> 	
			<span class="field">
<?php 		if ( !file_exists( $ftp_dir ) ) {
				_e( "The FTP upload folder does not exist.", 'cuar' ); 
			} else if ( $this->is_dir_empty( $ftp_dir ) ) {
				_e( "The FTP upload folder is empty.", 'cuar' ); 
			} else {
				echo '<select id="cuar_selected_ftp_file" name="cuar_selected_ftp_file">';
				echo '<option value="">' . __( 'Select a file', 'cuar' ) . '</option>';
				foreach ( $ftp_files as $filename ) {
					$filepath = $ftp_dir . '/' . $filename;
					if ( is_file( $filepath ) ) { 
						echo '<option value="' . esc_attr( $filename ) . '">' . esc_html( $filename ) . '</option>';
					} 
				}
				echo '</select>';
			} 
?>
			</span>
			<br/>
			<br/>
<?php if ( file_exists( $ftp_dir ) ) : ?>
			<span class="label"><label for="cuar_selected_ftp_file"><?php _e('Copy file or move it?', 'cuar');?></label></span>	
			<span class="field">
				<input id="cuar_ftp_delete_file_after_copy" name="cuar_ftp_delete_file_after_copy" type="checkbox" value="1" /> &nbsp;
				<span><?php _e("If checked, the file will be deleted after it has been copied to the owner's private storage directory", "cuar" )?></span>
			</span>
<?php endif; ?>
		</div>
				
		<script type="text/javascript">
		<!--
			jQuery( document ).ready( function($) {
				$( '#cuar_file_select_method' ).change(function() {
					var selection = $(this).val();

					// Do nothing if already visible
					if ( $( '#' + selection ).is(":visible") ) return;

					// Hide previous and then show new
					if ( $('.file-select-method:visible').length<=0 ) {
						$( '#' + selection ).fadeToggle();
					} else {
						$('.file-select-method:visible').fadeToggle("fast", function () {
							$( '#' + selection ).fadeToggle();
						});
					}
				});
			});
		//-->
		</script>
<?php 
		do_action( "cuar/private-content/files/after-upload-meta-box" );
	}
	
	/** 
	 * Supporting function for displaying the dropdown select box 
	 * for empty FTP upload directory or not. 
	 * Adapted from http://stackoverflow.com/a/7497848/1177153
	 */
	 
	public function is_dir_empty($dir) {
	if (!is_readable($dir)) return NULL; 
		$handle = opendir($dir);
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				return FALSE;
			}
		}
		return TRUE;
	}
	
	/**
	 * Callback to handle saving a post
	 *  
	 * @param int $post_id
	 * @param unknown $post
	 * @param array $previous_owner
	 * @param array $new_owner
	 * @return void|unknown
	 */
	public function do_save_post( $post_id, $post, $previous_owner, $new_owner ) {
		global $post;
		
		// When auto-saving, we don't do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
	
		// Only take care of our own post type
		if ( !$post || get_post_type( $post->ID )!='cuar_private_file' ) return;
	
		// Security check
		if ( !wp_verify_nonce( $_POST['wp_cuar_nonce_file'], plugin_basename(__FILE__) ) ) return $post_id;

		// If nothing to upload but owner changed, we'll simply move the file
		$has_owner_changed = false;
		if (    ( $new_owner['type']!=$previous_owner['type'] ) 
			|| !( array_diff( $previous_owner['ids'], $new_owner['ids']) === array_diff( $new_owner['ids'], $previous_owner['ids'] ) ) ) {
			$has_owner_changed = true;
		}
		
		if ( isset( $_POST['cuar_file_select_method'] ) && $_POST['cuar_file_select_method']=='cuar_from_ftp_folder' ) {
			if ( $has_owner_changed && empty( $_POST['cuar_selected_ftp_file'] ) ) {
				$this->private_file_addon->handle_private_file_owner_changed($post_id, $previous_owner, $new_owner);
				return $post_id;
			}
			
			if ( !empty( $_POST['cuar_selected_ftp_file'] ) ) {
				$ftp_dir = trailingslashit( $this->private_file_addon->get_ftp_path() );

				$this->private_file_addon->handle_copy_private_file_from_local_folder( $post_id, $previous_owner, $new_owner,
						$ftp_dir . $_POST['cuar_selected_ftp_file']);
			}
		} else {
			if ( $has_owner_changed && empty( $_FILES['cuar_private_file_file']['name'] ) ) {
				$this->private_file_addon->handle_private_file_owner_changed($post_id, $previous_owner, $new_owner);
				return $post_id;
			}
			
			if ( !empty( $_FILES['cuar_private_file_file']['name'] ) ) {
				$upload_result = $this->private_file_addon->handle_new_private_file_upload( $post_id, $previous_owner, $new_owner,
						$_FILES['cuar_private_file_file']);

				if ( $upload_result!==true ) {
					remove_action( 'cuar/core/ownership/after-save-owner', array( &$this, 'do_save_post' ) );

					$my_post = array(
							'ID'          => $post_id,
							'post_status' => 'draft'
						);
					wp_update_post( $my_post );
					
					add_action( 'cuar/core/ownership/after-save-owner', array( &$this, 'do_save_post' ), 10, 4 );
				}
			}
		}
	}

	/*------- CUSTOMISATION OF THE PLUGIN SETTINGS PAGE --------------------------------------------------------------*/

	public function add_settings_tab( $tabs ) {
		$tabs[ 'cuar_private_files' ] = __( 'Private Files', 'cuar' );
		return $tabs;
	}
	
	/**
	 * Add our fields to the settings page
	 * 
	 * @param CUAR_Settings $cuar_settings The settings class
	 */
	public function print_settings( $cuar_settings, $options_group ) {
		add_settings_section(
				'cuar_private_files_addon_general',
				__('General settings', 'cuar'),
				array( &$this, 'print_frontend_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);

		add_settings_field(
				CUAR_PrivateFileAddOn::$OPTION_ENABLE_ADDON,
				__('Enable add-on', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_private_files_addon_general',
				array(
					'option_id' => CUAR_PrivateFileAddOn::$OPTION_ENABLE_ADDON,
					'type' 		=> 'checkbox',
					'after'		=> 
						__( 'Check this to enable the private files add-on.', 'cuar' ) )
			);
		
		if ( !file_exists( $this->private_file_addon->get_ftp_path() ) ) {
			$folder_exists_message = '<span style="color: #c33;">' . __('This folder does not exist, please create it if you want to copy files from the FTP folder. Otherwise, you need not do anything.', 'cuar' ) . '</span>';
		} else {
			$folder_exists_message = "";
		}
		 
		add_settings_field(
				CUAR_PrivateFileAddOn::$OPTION_FTP_PATH,
				__('FTP uploads folder', 'cuar'),
				array( &$cuar_settings, 'print_input_field' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG,
				'cuar_private_files_addon_general',
				array(
					'option_id' => CUAR_PrivateFileAddOn::$OPTION_FTP_PATH,
					'type' 		=> 'text',
					'is_large'	=> true,
					'after'		=> '<p class="description">' 
							. __( 'This folder can be used when you want to use files uploaded with FTP. This is handy when direct upload is failing for big files for instance.', 'cuar' )
							. $folder_exists_message
							. '</p>' ) 
				);

		add_settings_section(
				'cuar_private_files_addon_storage',
				__('File Storage', 'cuar'),
				array( &$this, 'print_storage_section_info' ),
				CUAR_Settings::$OPTIONS_PAGE_SLUG
			);
	}
	
	/**
	 * Validate our options
	 * 
	 * @param CUAR_Settings $cuar_settings
	 * @param array $input
	 * @param array $validated
	 */
	public function validate_options( $validated, $cuar_settings, $input ) {
		// TODO OUTPUT ALLOWED FILE TYPES
		
		$cuar_settings->validate_boolean( $input, $validated, CUAR_PrivateFileAddOn::$OPTION_ENABLE_ADDON );

		// TODO: Would be good to have a validate_valid_folder function in CUAR_Settings class.
		$cuar_settings->validate_not_empty( $input, $validated, CUAR_PrivateFileAddOn::$OPTION_FTP_PATH);
				
		return $validated;
	}
	
	/**
	 * Print some info about the section
	 */
	public function print_frontend_section_info() {
		// echo '<p>' . __( 'Options for the private files add-on.', 'cuar' ) . '</p>';
	}
	
	/**
	 * Print some info about the section
	 */
	public function print_storage_section_info() {
		$po_addon = $this->plugin->get_addon('post-owner');
		$storage_dir = $po_addon->get_base_private_storage_directory( true );
		$sample_storage_dir = $po_addon->get_owner_storage_directory( array( get_current_user_id() ), 'usr', true, true );
		
		$required_perms = '705';
		$current_perms = substr( sprintf('%o', fileperms( $storage_dir ) ), -3);
		
		echo '<div class="cuar-section-description">';
		echo '<p>' 
				. sprintf( __( 'The files will be stored in the following directory: <code>%s</code>.', 'cuar' ),
						$storage_dir ) 
				. '</p>';

		echo '<p>'
				. sprintf( __( 'Each user has his own sub-directory. For instance, yours is: <code>%s</code>.', 'cuar' ),
						$sample_storage_dir )
				. '</p>';

		if ( $required_perms > $current_perms ) {
			echo '<p style="color: orange;">' 
				. sprintf( __('That directory should at least have the permissions set to 705. Currently it is '
						. '%s. You should adjust that directory permissions as upload or download might not work ' 
						. 'properly.', 'cuar' ), $current_perms ) 
				. '</p>';
		}
		echo '</div>';
	}
		
	/** @var CUAR_Plugin */
	private $plugin;

	/** @var CUAR_PrivateFileAddOn */
	private $private_file_addon;
}

endif; // if (!class_exists('CUAR_PrivateFileAdminInterface')) :