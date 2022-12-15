<?php
/**
 * OnePress Login
 *
 * @package OnePressLogin
 */

/*
Plugin Name: OnePress Login
Plugin URI: https://my.pressable.com
Description: Pressable OnePress Login for the MyPressable Control Panel.
Author: Pressable
Version: 1.0.0-beta.1
Author URI: https://my.pressable.com/
License: GPL2
*/

define( 'PRESSABLE_ONEPRESS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Include the pressable-onepress-login class.
 */
require_once PRESSABLE_ONEPRESS_DIR . 'class-pressable-onepress-login-plugin.php';

/**
 * Define functionality-related WordPress constants,
 * as some 2FA providers could not find the constants.
 * This was added due to functionlity noticed in testing WP 2FA
 */
if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
	define( 'AUTOSAVE_INTERVAL', MINUTE_IN_SECONDS );
}

/**
 * Decide if request should be handled
 *
 * @return bool True if eligible, False if not.
 */
function is_ready_to_handle_mpcp_login_request() {
	// Do not handle if WP is installing, or running a cron or handling AJAX request or if WPCLI request.
	if ( wp_installing() || wp_doing_cron() || wp_doing_ajax() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return false;
	}

	// Must include the MPCP login path with mpcp_token.
	if ( 'wp-login.php' === $GLOBALS['pagenow'] && isset( $_REQUEST['mpcp_token'] ) ) {
		return true;
	}

	return false;
}

// Run the plugin class.
new Pressable_OnePress_Login_Plugin();
