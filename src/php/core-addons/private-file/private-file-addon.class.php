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

require_once(dirname(__FILE__) . '/private-file-admin-interface.class.php');

if (!class_exists('CUAR_PrivateFileAddOn')) :

/**
 * Add-on to put private files in the customer area
*
* @author Vincent Prat @ MarvinLabs
*/
class CUAR_PrivateFileAddOn extends CUAR_AddOn {
	
	public function __construct() {
		parent::__construct( 'private-files', '4.0.0' );
	}
	
	public function get_addon_name() {
		return __( 'Private Files', 'cuar' );
	}

	public function run_addon( $plugin ) {
		if ( $this->is_enabled() ) {
			add_action( 'init', array( &$this, 'register_custom_types' ) );
			add_filter( 'cuar/core/post-types/content', array( &$this, 'register_private_post_types' ) );
			add_filter( 'cuar/core/types/content', array( &$this, 'register_content_type' ) );
			
			add_action( 'template_redirect', array( &$this, 'handle_file_actions' ) );
			add_action( 'before_delete_post', array( &$this, 'before_post_deleted' ) );
			
			add_filter( 'cuar/core/permission-groups', array( &$this, 'get_configurable_capability_groups' ) );
			add_action( 'cuar/core/admin/adminbar-menu-items', array( &$this, 'add_adminbar_menu_items' ), 10 );
		}
				
		// Init the admin interface if needed
		if ( is_admin() ) {
			$this->admin_interface = new CUAR_PrivateFileAdminInterface( $plugin, $this );
		} 
	}	
	
	/**
	 * Set the default values for the options
	 * 
	 * @param array $defaults
	 * @return array
	 */
	public function set_default_options( $defaults ) {
		$defaults = parent::set_default_options($defaults);
		
		$defaults[ self::$OPTION_ENABLE_ADDON ] = true;
		$defaults[ self::$OPTION_FTP_PATH] = WP_CONTENT_DIR . '/customer-area/ftp-uploads';
		
		return $defaults;
	}

	/**
	 * Add the menu item
	 */
	public function add_adminbar_menu_items( $submenus ) {
		$my_submenus = array();

		if ( current_user_can( 'cuar_pf_read' ) 
				|| current_user_can( 'cuar_pf_edit' ) 
				|| current_user_can( 'cuar_pf_manage_categories' ) ) {
			$my_submenus[] = array(
					'parent'=> 'customer-area',
					'id' 	=> 'customer-area-files',
					'title' => __( 'Private Files', 'cuar' ),
					'href' 	=> admin_url( 'edit.php?post_type=cuar_private_file' )
				);
		}
		
		if ( current_user_can( 'cuar_pf_read' ) ) {
			$my_submenus[] = array(
					'parent'=> 'customer-area-files',
					'id' 	=> 'customer-area-files-list',
					'title' => __( 'All files', 'cuar' ),
					'href' 	=> admin_url( 'edit.php?post_type=cuar_private_file' )
				);
		}
		
		if ( current_user_can( 'cuar_pf_edit' ) ) {
			$my_submenus[] = array(
					'parent'=> 'customer-area-files',
					'id' 	=> 'customer-area-files-new',
					'title' => __( 'New file', 'cuar' ),
					'href' 	=> admin_url( 'post-new.php?post_type=cuar_private_file' )
				);
		}
		
		if ( current_user_can( 'cuar_pf_manage_categories' ) ) {
			$my_submenus[] = array(
					'parent'=> 'customer-area-files',
					'id' 	=> 'customer-area-files-categories',
					'title' => __( 'Manage categories', 'cuar' ),
					'href' 	=> admin_url( 'edit-tags.php?taxonomy=cuar_private_file_category' )
				);
		}
	
		foreach ( $my_submenus as $submenu ) {
			$submenus[] = $submenu;
		}
	
		return $submenus;
	}
	
	/*------- SETTINGS ACCESSORS ------------------------------------------------------------------------------------*/
	
	public function is_enabled() {
		return $this->plugin->get_option( self::$OPTION_ENABLE_ADDON );
	}
	
	public function get_ftp_path() {
		return $this->plugin->get_option( self::$OPTION_FTP_PATH );
	}
	
	/*------- GENERAL MAINTAINANCE FUNCTIONS ------------------------------------------------------------------------*/
	
	/**
	 * Delete the files when a post is deleted
	 * @param int $post_id
	 */
	public function before_post_deleted( $post_id ) {			
		if ( get_post_type( $post_id )!='cuar_private_file' ) return;

		$filename = $this->get_file_name( $post_id );
		if ( empty( $filename ) ) return;
		
		$po_addon = $this->plugin->get_addon('post-owner');
		$filepath = $po_addon->get_private_file_path( $filename, $post_id );		
		
		if ( file_exists( $filepath ) ) {
			unlink( $filepath );
		}
	}
	
	/*------- FUNCTIONS TO ACCESS THE POST META ---------------------------------------------------------------------*/

	/**
	 * Get the name of the file associated to the given post
	 *
	 * @param int $post_id
	 * @return string|mixed
	 */
	public function get_file_name( $post_id ) {
		$file = get_post_meta( $post_id, 'cuar_private_file_file', true );	
		if ( !$file || empty( $file ) ) return '';	
		return apply_filters( 'cuar/private-content/files/file-name', $file['file'] );
	}
	
	/**
	 * Get the type of the file associated to the given post
	 *
	 * @param int $post_id
	 * @return string|mixed
	 */
	public function get_file_type( $post_id ) {
		$file = get_post_meta( $post_id, 'cuar_private_file_file', true );	
		if ( !$file || empty( $file ) ) return '';	
		return apply_filters( 'cuar/private-content/files/file-type', pathinfo( $file['file'], PATHINFO_EXTENSION ) );
	}
	
	/**
	 * Get the size of the file associated to the given post
	 *
	 * @param int $post_id
	 * @return boolean|int false if the file does not exist
	 */
	public function get_file_size( $post_id ) {
		$po_addon = $this->plugin->get_addon('post-owner');
		
		$file_name = $this->get_file_name( $post_id );
		$file_path = $po_addon->get_private_file_path( $file_name, $post_id );
		
		return apply_filters( 'cuar/private-content/files/file-size', filesize( $file_path ) );
	}
	
	/**
	 * Get the number of times the file has been downloaded
	 *
	 * @param int $post_id
	 * @return int
	 */
	public function get_file_download_count( $post_id ) {
		$count = get_post_meta( $post_id, 'cuar/private-content/files/on-download_count', true );	
		if ( !$count || empty( $count ) ) return 0;	
		return intval( $count );
	}
	
	/**
	 * Get the number of times the file has been downloaded
	 *
	 * @param int $post_id
	 * @return int
	 */
	public function increment_file_download_count( $post_id ) {
		update_post_meta( $post_id, 
			'cuar/private-content/files/on-download_count', 
			$this->get_file_download_count( $post_id ) + 1 );
	}
	
	/**
	 * Get the permalink to a file for the specified action
	 * @param int $post_id
	 * @param string $action
	 */
	public function get_file_permalink( $post_id, $action = 'download' ) {		
		$cpf_addon = $this->plugin->get_addon('customer-private-files');
		$url = $cpf_addon->get_single_private_content_action_url( $post_id, $action );		
		return $url;
	}
	
	/**
	 * Handles the case when we need to change the owner of the file of an existing post
	 * @param int $post_id
	 * @param array $previous_owner
	 * @param array $new_owner
	 */
	public function handle_private_file_owner_changed( $post_id, $previous_owner, $new_owner ) {
		$po_addon = $this->plugin->get_addon('post-owner');
		
		$previous_file = get_post_meta( $post_id, 'cuar_private_file_file', true );		
		if ( $previous_file ) {
			$previous_file['path'] = $po_addon->get_owner_file_path(
					$previous_file['file'], $previous_owner['ids'], $previous_owner['type'], true );

			if ( file_exists( $previous_file['path'] ) ) {
				$new_file_path = $po_addon->get_owner_file_path(
						$previous_file['file'], $new_owner['ids'], $new_owner['type'], true );
					
				if ( $previous_file['path']==$new_file_path ) return;				
				if ( copy( $previous_file['path'], $new_file_path ) ) unlink( $previous_file['path'] );
			}
		}
	}

	/**
	 * Change the upload directory on the fly when uploading our private file
	 * @param unknown $default_dir
	 * @return unknown|multitype:boolean string unknown
	 */
	public function custom_upload_dir( $default_dir ) {		
		if ( ! isset( $_POST['post_ID'] ) || $_POST['post_ID'] < 0 ) return $default_dir;	
		if ( $_POST['post_type'] != 'cuar_private_file' ) return $default_dir;
	
		$po_addon = $this->plugin->get_addon('post-owner');
		
		$dir = $po_addon->get_base_private_storage_directory();
		$url = $po_addon->get_base_private_storage_url();
	
		$bdir = $dir;
		$burl = $url;
	
		$subdir = '/' . $po_addon->get_private_storage_directory( $_POST['post_ID'] );
		
		$dir .= $subdir;
		$url .= $subdir;
	
		$custom_dir = array( 
			'path'    => $dir,
			'url'     => $url, 
			'subdir'  => $subdir, 
			'basedir' => $bdir, 
			'baseurl' => $burl,
			'error'   => false, 
		);
	
		return $custom_dir;
	}
	
	/**
	 * 
	 * @param int $post_id
	 * @param array $previous_owner
	 * @param array $new_owner
	 */
	public function handle_new_private_file_upload( $post_id, $previous_owner, $new_owner, $file ) {
		if ( !isset( $file ) || empty( $file ) ) return array( 'error' => __( 'no file to upload', 'cuar' ) );
		
		$po_addon = $this->plugin->get_addon('post-owner');
		
		$previous_file = get_post_meta( $post_id, 'cuar_private_file_file', true );
		
		// Do some file type checking on the uploaded file if needed
		$new_file_name = $file['name'];
		$supported_types = apply_filters( 'cuar/private-content/files/supported-types', null );
		if ( $supported_types!=null ) {
			$arr_file_type = wp_check_filetype( basename( $file['name'] ) );
			$uploaded_type = $arr_file_type['type'];
				
			if ( !in_array( $uploaded_type, $supported_types ) ) {
				$msg =  sprintf( __("This file type is not allowed. You can only upload: %s", 'cuar',
						implode( ', ', $supported_types ) ) );
		
				$this->plugin->add_admin_notice( $msg );
				return array( 'error' => $msg );
			}
		}
		
		// Delete the existing file if any
		if ( $previous_file ) {
			$previous_file['path'] = $po_addon->get_owner_file_path(
					$previous_file['file'], $previous_owner['ids'], $previous_owner['type'], true );
		
			if ( $previous_file['path'] && file_exists( $previous_file['path'] ) ) {
				unlink( $previous_file['path'] );
			}
		}
		
		// Use the WordPress API to upload the file
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		
		// We will send files to a custom directory
		add_filter( 'upload_dir', array( &$this, 'custom_upload_dir' ));			
		if ( ! isset( $_POST['post_ID'] ) || $_POST['post_ID'] < 0 ) $_POST['post_ID'] = $post_id;	
		if ( ! isset( $_POST['post_type'] ) || $_POST['post_type'] != 'cuar_private_file' ) $_POST['post_type'] = 'cuar_private_file';	

		// Let WP handle the rest		
		$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
		
		if ( empty( $upload ) ) {
			$msg = sprintf( __( 'An unknown error happened while uploading your file.', 'cuar' ) );
			$this->plugin->add_admin_notice( $msg );
			return array( 'error' => $msg );
		} else if ( isset( $upload['error'] ) ) {
			$msg = sprintf( __( 'An error happened while uploading your file: %s', 'cuar' ), $upload['error'] );
			$this->plugin->add_admin_notice( $msg );
			return array( 'error' => $msg );
		} else {
			$upload['file'] = basename( $upload['file'] );
			unset( $upload['url'] );
			update_post_meta( $post_id, 'cuar_private_file_file', $upload );
			return true;
		}
	}
	
	/**
	 * Handle the assignment and move of the private file from the FTP upload
	 * folder and into the user's private folder
	 * drsprite
	 */
	 public function handle_copy_private_file_from_ftp_folder( $post_id, $previous_owner, $new_owner, $ftp_file ) {
		if ( !isset( $ftp_file ) || empty( $ftp_file ) ) return;
		
		$po_addon = $this->plugin->get_addon('post-owner');
		
		// Delete the existing file if any
		$previous_file = get_post_meta( $post_id, 'cuar_private_file_file', true );
		if ( $previous_file ) {
			$previous_file['path'] = $po_addon->get_owner_file_path(
					$previous_file['file'], $previous_owner['ids'], $previous_owner['type'], true );
		
			if ( $previous_file['path'] && file_exists( $previous_file['path'] ) ) {
				unlink( $previous_file['path'] );
			}
		}

	  	// copy from ftp_file to dest_folder
	  	$dest_folder = trailingslashit( $po_addon->get_private_storage_directory( $post_id, true, true ) );
	  	$dest_file = $dest_folder . wp_unique_filename( $dest_folder, basename( $ftp_file ), null );	  	
	  	$delete_after_copy = isset( $_POST['cuar_ftp_delete_file_after_copy'] );
	  	
	  	if ( copy( $ftp_file, $dest_file ) ) {
	  		if ( $delete_after_copy ) {
	  			unlink( $ftp_file );
	  		}
			$upload = array( 'file' => basename( $dest_file ) );
			update_post_meta( $post_id, 'cuar_private_file_file', $upload );
	  	} else {
			$msg = __( 'An error happened while copying your file from the FTP folder', 'cuar' );
			$this->plugin->add_admin_notice( $msg );
	  	}
	}

	/*------- HANDLE FILE VIEWING AND DOWNLOADING --------------------------------------------------------------------*/
		
	/**
	 * Handle the actions on a private file
	 */
	public function handle_file_actions() {			
		// If not on a matching post type, we do nothing
		if ( !is_singular('cuar_private_file') ) return;
		
		// If not a known action, do nothing
		$action = get_query_var( 'cuar_action' );
		if ( $action!='download' && $action!='view' ) {
			return;
		}
		
		// If not logged-in, we ask for details
		if ( !is_user_logged_in() ) {
			$this->plugin->login_then_redirect_to_url( $_SERVER['REQUEST_URI'] );
		}

		$po_addon = $this->plugin->get_addon('post-owner');
		
		// If not authorized to download the file, we bail	
		$post = get_queried_object();
		$current_user_id = get_current_user_id();		
		$author_id = $post->post_author;		
		$is_current_user_owner = $po_addon->is_user_owner_of_post( $post->ID, $current_user_id );
		
		if ( !( $is_current_user_owner || $author_id==$current_user_id || current_user_can('cuar_view_any_cuar_private_file'))) {
			wp_die( __( "You are not authorized to access this file", "cuar" ) );
			exit();
		}
		
		// Seems we are all good, checkout the requested action and do something
		$file_type = $this->get_file_type( $post->ID );
		$file_name = $this->get_file_name( $post->ID );
		$file_path = $po_addon->get_private_file_path( $file_name, $post->ID );
		
		if ( $action=='download' ) {			
			if ( $author_id!=$current_user_id ) {
				$this->increment_file_download_count( $post->ID );
			}
			
			do_action( 'cuar/private-content/files/on-download', $post->ID, $current_user_id, $this );	
					
			$this->output_file( $file_path, $file_name, $file_type, 'download' );
		} else if ( $action=='view' ) {			
			if ( $author_id!=$current_user_id ) {
				$this->increment_file_download_count( $post->ID );
			}
			
			do_action( 'cuar/private-content/files/on-view', $post->ID, $current_user_id, $this );		
			
			$this->output_file( $file_path, $file_name, $file_type, 'view' );
		} else {
			// Do nothing
		}
	}

	/**
	 * Output the content of a file to the http stream
	 *
	 * @param string $file The path to the file
	 * @param string $name The name to give to the file
	 * @param string $mime_type The mime type (determined automatically for well-known file types if blank)
	 * @param string $action view or download the file (hint to the browser)
	 */
	private function output_file( $file, $name, $mime_type='', $action = 'download' ) {
		if ( !is_readable($file) ) {
			die('File not found or inaccessible!<br />'.$file.'<br /> '.$name);
			return;
		}

		$size = filesize( $file );
		$name = rawurldecode( $name );

		$known_mime_types = array(
				"323"		=> "text/h323",
				"acx"		=> "application/internet-property-stream",
				"ai"		=> "application/postscript",
				"aif"		=> "audio/x-aiff",
				"aifc"		=> "audio/x-aiff",
				"aiff"		=> "audio/x-aiff",
				"asf"		=> "video/x-ms-asf",
				"asr"		=> "video/x-ms-asf",
				"asx"		=> "video/x-ms-asf",
				"au"		=> "audio/basic",
				"avi"		=> "video/x-msvideo",
				"axs"		=> "application/olescript",
				"bas"		=> "text/plain",
				"bcpio"		=> "application/x-bcpio",
				"bin"		=> "application/octet-stream",
				"bmp"		=> "image/bmp",
				"c"			=> "text/plain",
				"cat"		=> "application/vnd.ms-pkiseccat",
				"cdf"		=> "application/x-cdf",
				"cdf"		=> "application/x-netcdf",
				"cer"		=> "application/x-x509-ca-cert",
				"class"		=> "application/octet-stream",
				"clp"		=> "application/x-msclip",
				"cmx"		=> "image/x-cmx",
				"cod"		=> "image/cis-cod",
				"cpio"		=> "application/x-cpio",
				"crd"		=> "application/x-mscardfile",
				"crl"		=> "application/pkix-crl",
				"crt"		=> "application/x-x509-ca-cert",
				"csh"		=> "application/x-csh",
				"css"		=> "text/css",
				"dcr"		=> "application/x-director",
				"der"		=> "application/x-x509-ca-cert",
				"dir"		=> "application/x-director",
				"dll"		=> "application/x-msdownload",
				"dms"		=> "application/octet-stream",
				"doc"		=> "application/msword",
				"dot"		=> "application/msword",
				"dvi"		=> "application/x-dvi",
				"dxr"		=> "application/x-director",
				"eps"		=> "application/postscript",
				"etx"		=> "text/x-setext",
				"evy"		=> "application/envoy",
				"exe"		=> "application/octet-stream",
				"fif"		=> "application/fractals",
				"flr"		=> "x-world/x-vrml",
				"gif"		=> "image/gif",
				"gtar"		=> "application/x-gtar",
				"gz"		=> "application/x-gzip",
				"h"			=> "text/plain",
				"hdf"		=> "application/x-hdf",
				"hlp"		=> "application/winhlp",
				"hqx"		=> "application/mac-binhex40",
				"hta"		=> "application/hta",
				"htc"		=> "text/x-component",
				"htm"		=> "text/html",
				"html"		=> "text/html",
				"htt"		=> "text/webviewhtml",
				"ico"		=> "image/x-icon",
				"ief"		=> "image/ief",
				"iii"		=> "application/x-iphone",
				"ins"		=> "application/x-internet-signup",
				"isp"		=> "application/x-internet-signup",
				"jfif"		=> "image/pipeg",
				"jpe"		=> "image/jpeg",
				"jpeg"		=> "image/jpeg",
				"jpg"		=> "image/jpeg",
				"js"		=> "application/x-javascript",
				"latex"		=> "application/x-latex",
				"lha"		=> "application/octet-stream",
				"lsf"		=> "video/x-la-asf",
				"lsx"		=> "video/x-la-asf",
				"lzh"		=> "application/octet-stream",
				"m13"		=> "application/x-msmediaview",
				"m14"		=> "application/x-msmediaview",
				"m3u"		=> "audio/x-mpegurl",
				"man"		=> "application/x-troff-man",
				"mdb"		=> "application/x-msaccess",
				"me"		=> "application/x-troff-me",
				"mht"		=> "message/rfc822",
				"mhtml"		=> "message/rfc822",
				"mid"		=> "audio/mid",
				"mny"		=> "application/x-msmoney",
				"mov"		=> "video/quicktime",
				"movie"		=> "video/x-sgi-movie",
				"mp2"		=> "video/mpeg",
				"mp3"		=> "audio/mpeg",
				"mpa"		=> "video/mpeg",
				"mpe"		=> "video/mpeg",
				"mpeg"		=> "video/mpeg",
				"mpg"		=> "video/mpeg",
				"mpp"		=> "application/vnd.ms-project",
				"mpv2"		=> "video/mpeg",
				"ms"		=> "application/x-troff-ms",
				"msg"		=> "application/vnd.ms-outlook",
				"mvb"		=> "application/x-msmediaview",
				"nc"		=> "application/x-netcdf",
				"nws"		=> "message/rfc822",
				"oda"		=> "application/oda",
				"p10"		=> "application/pkcs10",
				"p12"		=> "application/x-pkcs12",
				"p7b"		=> "application/x-pkcs7-certificates",
				"p7c"		=> "application/x-pkcs7-mime",
				"p7m"		=> "application/x-pkcs7-mime",
				"p7r"		=> "application/x-pkcs7-certreqresp",
				"p7s"		=> "application/x-pkcs7-signature",
				"pbm"		=> "image/x-portable-bitmap",
				"pdf"		=> "application/pdf",
				"pfx"		=> "application/x-pkcs12",
				"pgm"		=> "image/x-portable-graymap",
				"pko"		=> "application/ynd.ms-pkipko",
				"pma"		=> "application/x-perfmon",
				"pmc"		=> "application/x-perfmon",
				"pml"		=> "application/x-perfmon",
				"pmr"		=> "application/x-perfmon",
				"pmw"		=> "application/x-perfmon",
				"pnm"		=> "image/x-portable-anymap",
				"pot"		=> "application/vnd.ms-powerpoint",
				"ppm"		=> "image/x-portable-pixmap",
				"pps"		=> "application/vnd.ms-powerpoint",
				"ppt"		=> "application/vnd.ms-powerpoint",
				"prf"		=> "application/pics-rules",
				"ps"		=> "application/postscript",
				"pub"		=> "application/x-mspublisher",
				"qt"		=> "video/quicktime",
				"ra"		=> "audio/x-pn-realaudio",
				"ram"		=> "audio/x-pn-realaudio",
				"ras"		=> "image/x-cmu-raster",
				"rgb"		=> "image/x-rgb",
				"rmi"		=> "audio/mid",
				"roff"		=> "application/x-troff",
				"rtf"		=> "application/rtf",
				"rtx"		=> "text/richtext",
				"scd"		=> "application/x-msschedule",
				"sct"		=> "text/scriptlet",
				"setpay"	=> "application/set-payment-initiation",
				"setreg"	=> "application/set-registration-initiation",
				"sh"		=> "application/x-sh",
				"shar"		=> "application/x-shar",
				"sit"		=> "application/x-stuffit",
				"snd"		=> "audio/basic",
				"spc"		=> "application/x-pkcs7-certificates",
				"spl"		=> "application/futuresplash",
				"src"		=> "application/x-wais-source",
				"sst"		=> "application/vnd.ms-pkicertstore",
				"stl"		=> "application/vnd.ms-pkistl",
				"stm"		=> "text/html",
				"sv4cpio"	=> "application/x-sv4cpio",
				"sv4crc"	=> "application/x-sv4crc",
				"svg"		=> "image/svg+xml",
				"swf"		=> "application/x-shockwave-flash",
				"t"			=> "application/x-troff",
				"tar"		=> "application/x-tar",
				"tcl"		=> "application/x-tcl",
				"tex"		=> "application/x-tex",
				"texi"		=> "application/x-texinfo",
				"texinfo"	=> "application/x-texinfo",
				"tgz"		=> "application/x-compressed",
				"tif"		=> "image/tiff",
				"tiff"		=> "image/tiff",
				"tr"		=> "application/x-troff",
				"trm"		=> "application/x-msterminal",
				"tsv"		=> "text/tab-separated-values",
				"txt"		=> "text/plain",
				"uls"		=> "text/iuls",
				"ustar"		=> "application/x-ustar",
				"vcf"		=> "text/x-vcard",
				"vrml"		=> "x-world/x-vrml",
				"wav"		=> "audio/x-wav",
				"wcm"		=> "application/vnd.ms-works",
				"wdb"		=> "application/vnd.ms-works",
				"wks"		=> "application/vnd.ms-works",
				"wmf"		=> "application/x-msmetafile",
				"wps"		=> "application/vnd.ms-works",
				"wri"		=> "application/x-mswrite",
				"wrl"		=> "x-world/x-vrml",
				"wrz"		=> "x-world/x-vrml",
				"xaf"		=> "x-world/x-vrml",
				"xbm"		=> "image/x-xbitmap",
				"xla"		=> "application/vnd.ms-excel",
				"xlc"		=> "application/vnd.ms-excel",
				"xlm"		=> "application/vnd.ms-excel",
				"xls"		=> "application/vnd.ms-excel",
				"xlt"		=> "application/vnd.ms-excel",
				"xlw"		=> "application/vnd.ms-excel",
				"xof"		=> "x-world/x-vrml",
				"xpm"		=> "image/x-xpixmap",
				"xwd"		=> "image/x-xwindowdump",
				"z"			=> "application/x-compress",
				"zip"		=> "application/zip"
			);

		if ( $mime_type=='' ){
			$file_extension = pathinfo( $file, PATHINFO_EXTENSION );
			if ( array_key_exists( $file_extension, $known_mime_types ) ){
				$mime_type = $known_mime_types[ $file_extension ];
			} else {
				$mime_type = "application/octet-stream";
			}
		};

		// Fix http://wordpress.org/support/topic/problems-with-image-files
		@ob_end_clean(); //turn off output buffering to decrease cpu usage
		@ob_clean();
		
		// required for IE, otherwise Content-Disposition may be ignored
		if ( ini_get('zlib.output_compression') ) ini_set('zlib.output_compression', 'Off');

		header('Content-Type: ' . $mime_type);
		if ( $action == 'download' ) header('Content-Disposition: attachment; filename="'.$name.'"');
		else header('Content-Disposition: inline; filename="'.$name.'"');
		header("Content-Transfer-Encoding: binary");
		header('Accept-Ranges: bytes');

		/* The three lines below basically make the	download non-cacheable */
		header("Cache-control: private");
		header('Pragma: private');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

		// multipart-download and download resuming support
		if ( isset($_SERVER['HTTP_RANGE']) ) {
			list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
			list($range) = explode(",",$range,2);
			list($range, $range_end) = explode("-", $range);
			$range=intval($range);

			if(!$range_end) {
				$range_end=$size-1;
			} else {
				$range_end=intval($range_end);
			}

			$new_length = $range_end-$range+1;
			header("HTTP/1.1 206 Partial Content");
			header("Content-Length: $new_length");
			header("Content-Range: bytes $range-$range_end/$size");
		} else {
			$new_length=$size;
			header("Content-Length: ".$size);
		}

		/* output the file itself */
		$chunksize = 1*(1024*1024); //you may want to change this
		$bytes_send = 0;
		if ($file = fopen($file, 'r')) {
			if(isset($_SERVER['HTTP_RANGE']))
				fseek($file, $range);

			while(!feof($file) && (!connection_aborted()) && ($bytes_send<$new_length)) {
				$buffer = fread($file, $chunksize);
				print($buffer); //echo($buffer); // is also possible
				flush();
				$bytes_send += strlen($buffer);
			}
			fclose($file);
		}
		else die('Error - can not open file.');

		die();
	}

	/*------- INITIALISATIONS ----------------------------------------------------------------------------------------*/
	
	public function get_configurable_capability_groups( $capability_groups ) {
		$capability_groups[ 'cuar_private_file' ] = array(
				'label'		=> __( 'Private Files', 'cuar' ),
				'groups'	=> array(
						'back-office' => array(
								'group_name' => __( 'Back-office', 'cuar' ),
								'capabilities' => array(
										'cuar_pf_list_all'				=> __( 'List all files', 'cuar' ),
										'cuar_pf_edit' 					=> __( 'Create/Edit files', 'cuar' ),
										'cuar_pf_delete'				=> __( 'Delete files', 'cuar' ),
										'cuar_pf_read' 					=> __( 'Access files', 'cuar' ),
										'cuar_pf_manage_categories' 	=> __( 'Manage categories', 'cuar' ),
										'cuar_pf_edit_categories' 		=> __( 'Edit categories', 'cuar' ),
										'cuar_pf_delete_categories' 	=> __( 'Delete categories', 'cuar' ),
										'cuar_pf_assign_categories' 	=> __( 'Assign categories', 'cuar' ),
									)
							),
						'front-office'	=> array(
								'group_name' => __( 'Front-office', 'cuar' ),
								'capabilities' => array(
										'cuar_view_files'					=> __( 'View private files', 'cuar' ),
										'cuar_view_any_cuar_private_file' 	=> __( 'View any private file', 'cuar' ),
									)
							)
					)
			);
		
		return $capability_groups;
	}
	
	/**
	 * Declare our content type
	 * @param array $types
	 * @return array
	 */
	public function register_content_type($types) {
		$types['cuar_private_file'] = array(
				'label-singular'		=> _x( 'File', 'cuar_private_file', 'cuar' ),
				'label-plural'			=> _x( 'Files', 'cuar_private_file', 'cuar' ),
				'content-page-addon'	=> 'customer-private-files'
			);
		return $types;
	}
	
	/**
	 * Declare that our post type is owned by someone
	 * @param array $types
	 * @return array
	 */
	public function register_private_post_types($types) {
		$types[] = "cuar_private_file";
		return $types;
	}
	
	/**
	 * Register the custom post type for files and the associated taxonomies
	 */
	public function register_custom_types() {
		$labels = array(
				'name' 				=> _x( 'Private Files', 'cuar_private_file', 'cuar' ),
				'singular_name' 	=> _x( 'Private File', 'cuar_private_file', 'cuar' ),
				'add_new' 			=> _x( 'Add New', 'cuar_private_file', 'cuar' ),
				'add_new_item' 		=> _x( 'Add New Private File', 'cuar_private_file', 'cuar' ),
				'edit_item' 		=> _x( 'Edit Private File', 'cuar_private_file', 'cuar' ),
				'new_item' 			=> _x( 'New Private File', 'cuar_private_file', 'cuar' ),
				'view_item' 		=> _x( 'View Private File', 'cuar_private_file', 'cuar' ),
				'search_items' 		=> _x( 'Search Private Files', 'cuar_private_file', 'cuar' ),
				'not_found' 		=> _x( 'No private files found', 'cuar_private_file', 'cuar' ),
				'not_found_in_trash'=> _x( 'No private files found in Trash', 'cuar_private_file', 'cuar' ),
				'parent_item_colon' => _x( 'Parent Private File:', 'cuar_private_file', 'cuar' ),
				'menu_name' 		=> _x( 'Private Files', 'cuar_private_file', 'cuar' ),
			);

		$args = array(
				'labels' 				=> $labels,
				'hierarchical' 			=> false,
				'supports' 				=> array( 'title', 'editor', 'author', 'thumbnail', 'comments' ),
				'taxonomies' 			=> array( 'cuar_private_file_category' ),
				'public' 				=> true,
				'show_ui' 				=> true,
				'show_in_menu' 			=> false,
				'show_in_nav_menus' 	=> false,
				'publicly_queryable' 	=> true,
				'exclude_from_search' 	=> true,
				'has_archive' 			=> false,
				'query_var' 			=> 'cuar_private_file',
				'can_export' 			=> false,
				'rewrite' 				=> false,
				'capabilities' 			=> array(
						'edit_post' 			=> 'cuar_pf_edit',
						'edit_posts' 			=> 'cuar_pf_edit',
						'edit_others_posts' 	=> 'cuar_pf_edit',
						'publish_posts' 		=> 'cuar_pf_edit',
						'read_post' 			=> 'cuar_pf_read',
						'read_private_posts' 	=> 'cuar_pf_edit',
						'delete_post' 			=> 'cuar_pf_delete',
						'delete_posts' 			=> 'cuar_pf_delete'
					)
			);

		register_post_type( 'cuar_private_file', apply_filters( 'cuar/private-content/files/register-post-type-args', $args ) );

		$labels = array(
				'name' 						=> _x( 'File Categories', 'cuar_private_file_category', 'cuar' ),
				'singular_name' 			=> _x( 'File Category', 'cuar_private_file_category', 'cuar' ),
				'search_items' 				=> _x( 'Search File Categories', 'cuar_private_file_category', 'cuar' ),
				'popular_items' 			=> _x( 'Popular File Categories', 'cuar_private_file_category', 'cuar' ),
				'all_items' 				=> _x( 'All File Categories', 'cuar_private_file_category', 'cuar' ),
				'parent_item' 				=> _x( 'Parent File Category', 'cuar_private_file_category', 'cuar' ),
				'parent_item_colon' 		=> _x( 'Parent File Category:', 'cuar_private_file_category', 'cuar' ),
				'edit_item' 				=> _x( 'Edit File Category', 'cuar_private_file_category', 'cuar' ),
				'update_item' 				=> _x( 'Update File Category', 'cuar_private_file_category', 'cuar' ),
				'add_new_item' 				=> _x( 'Add New File Category', 'cuar_private_file_category', 'cuar' ),
				'new_item_name' 			=> _x( 'New File Category', 'cuar_private_file_category', 'cuar' ),
				'separate_items_with_commas'=> _x( 'Separate file categories with commas', 'cuar_private_file_category', 'cuar' ),
				'add_or_remove_items' 		=> _x( 'Add or remove file categories', 'cuar_private_file_category', 'cuar' ),
				'choose_from_most_used' 	=> _x( 'Choose from the most used file categories', 'cuar_private_file_category', 'cuar' ),
				'menu_name' 				=> _x( 'File Categories', 'cuar_private_file_category', 'cuar' ),
			);
	  
		$args = array(
				'labels' 			=> $labels,
				'public' 			=> true,
				'show_in_menu' 		=> false,
				'show_in_nav_menus' => 'customer-area',
				'show_ui' 			=> true,
				'show_tagcloud' 	=> false,
				'show_admin_column'	=> true,
				'hierarchical' 		=> true,
				'query_var' 		=> true,
				'rewrite' 			=> false,
				'capabilities' 			=> array(
						'manage_terms' 		=> 'cuar_pf_manage_categories',
						'edit_terms' 		=> 'cuar_pf_edit_categories',
						'delete_terms' 		=> 'cuar_pf_delete_categories',
						'assign_terms' 		=> 'cuar_pf_assign_categories',
					)
			);
	  
		register_taxonomy( 'cuar_private_file_category', array( 'cuar_private_file' ), apply_filters( 'cuar/private-content/files/category/register-taxonomy-args', $args ) );
	}

	// General options
	public static $OPTION_ENABLE_ADDON					= 'enable_private_files';
	public static $OPTION_FTP_PATH 						= 'frontend_ftp_upload_path';
	
	/** @var CUAR_PrivateFileAdminInterface */
	private $admin_interface;
}

// Make sure the addon is loaded
new CUAR_PrivateFileAddOn();

endif; // if (!class_exists('CUAR_PrivateFileAddOn')) 
