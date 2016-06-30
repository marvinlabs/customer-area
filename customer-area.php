<?php
/*
	Plugin Name: 	WP Customer Area
	Description: 	WP Customer Area is a modular all-in-one solution to manage private content with WordPress.
	Plugin URI: 	http://wp-customerarea.com
	Version: 		7.0.8
	Author: 		MarvinLabs
	Author URI: 	http://www.marvinlabs.com
	Text Domain: 	cuar
	Domain Path: 	/languages
*/

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

if ( !defined('CUAR_PLUGIN_DIR')) define('CUAR_PLUGIN_DIR', plugin_dir_path(__FILE__));
if ( !defined('CUAR_INCLUDES_DIR')) define('CUAR_INCLUDES_DIR', CUAR_PLUGIN_DIR . '/src/php');

define('CUAR_LANGUAGE_DIR', 'customer-area/languages');

define('CUAR_PLUGIN_VERSION', '7.0.8');
define('CUAR_PLUGIN_URL', untrailingslashit(WP_PLUGIN_URL) . '/customer-area/'); // plugin_dir_url( __FILE__ ) );
define('CUAR_SCRIPTS_URL', CUAR_PLUGIN_URL . 'scripts');
define('CUAR_ADMIN_SKIN', 'plugin%%default-wp38');
define('CUAR_FRONTEND_SKIN', 'plugin%%master');
define('CUAR_PLUGIN_FILE', 'customer-area/customer-area.php');

define('CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION', false);
// define( 'CUAR_DEBUG_UPGRADE_PROCEDURE_FROM_VERSION', '6.3.0' );

// Helpers
include_once(CUAR_INCLUDES_DIR . '/helpers/address-helper.class.php');
include_once(CUAR_INCLUDES_DIR . '/helpers/currency-helper.class.php');
include_once(CUAR_INCLUDES_DIR . '/helpers/country-helper.class.php');
include_once(CUAR_INCLUDES_DIR . '/helpers/wordpress-helper.class.php');

// Core Framework classes
include_once(CUAR_INCLUDES_DIR . '/core-classes/Content/custom-post.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/Content/custom-taxonomy.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-classes/Log/log-event.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/Log/log-event-type.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/Log/logger.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-classes/Activation/plugin-activation-delegate.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/Activation/plugin-activation-manager.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-classes/TemplateEngine/template-file.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/TemplateEngine/template-finder.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/TemplateEngine/template-engine.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-classes/Licensing/license-store.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/Licensing/license-validation-result.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/Licensing/licensing.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-classes/MessageCenter/message-center.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-classes/Shortcode/shortcode.class.php');

// Core Plugin classes
include_once(CUAR_INCLUDES_DIR . '/core-classes/settings.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/plugin-store.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/plugin-activation.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/plugin.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/field-renderer.interface.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/abstract-field-renderer.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/abstract-input-field-renderer.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/password-field-renderer.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/email-field-renderer.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/long-text-field-renderer.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/short-text-field-renderer.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/renderer/display-name-field-renderer.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/storage/storage.interface.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/storage/user-meta-storage.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/storage/user-storage.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/storage/post-meta-storage.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/validation-rule.interface.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/simple-validation.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/email-validation.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/string-validation.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/number-validation.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/validation/password-validation.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/field.interface.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/simple-field.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/header-field.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/text-field.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/number-field.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/email-field.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/user-password-field.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-classes/object-meta/field/display-name-field.class.php');

// Core addons
include_once(CUAR_INCLUDES_DIR . '/core-addons/admin-area/admin-area-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/installer/installer-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/log/log-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/post-owner/post-owner-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/post-owner/post-owner-admin-interface.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/post-owner/post-owner-user-owner-type.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/container-owner/container-owner-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/capabilities/capabilities-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-pages/customer-pages-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/status/status-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/user-profile/user-profile-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/shortcodes/shortcodes-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/addresses/addresses-addon.class.php');

// Core content types
include_once(CUAR_INCLUDES_DIR . '/core-addons/private-page/private-page-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/private-file/private-file-addon.class.php');

// Core pages
include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-home/customer-home-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-dashboard/customer-dashboard-addon.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-account-home/customer-account-home-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-account-edit/customer-account-edit-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-account/customer-account-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-logout/customer-logout-addon.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-private-files-home/customer-private-files-home-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-private-files/customer-private-files-addon.class.php');

include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-private-pages-home/customer-private-pages-home-addon.class.php');
include_once(CUAR_INCLUDES_DIR . '/core-addons/customer-private-pages/customer-private-pages-addon.class.php');

// Template functions
include_once(CUAR_INCLUDES_DIR . '/functions/functions-general.php');
include_once(CUAR_INCLUDES_DIR . '/functions/functions-private-content.php');
include_once(CUAR_INCLUDES_DIR . '/functions/functions-private-files.php');

// Some hooks for activation, deactivation, ...
CUAR_PluginActivationManager::set_delegate(new CUAR_PluginActivation());
register_activation_hook(__FILE__, array('CUAR_PluginActivationManager', 'on_activate'));
register_deactivation_hook(__FILE__, array('CUAR_PluginActivationManager', 'on_deactivate'));

// Start the plugin!
global $cuar_plugin;
$cuar_plugin = new CUAR_Plugin();
$cuar_plugin->run();
