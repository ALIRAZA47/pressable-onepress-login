<?php
/**
 * BionicWP OnePress Login
 *
 * @package OnePressLogin
 */

/*
Plugin Name: BionicWP OnePress Login
Plugin URI: https://my.bionicwp.com
Description: BionicWP OnePress Login.
Author: BionicWP
Version: 1.3.2
Author URI: https://my.bionicwp.com/
License: GPL2
*/

if ( ! defined( 'PRESSABLE_ONEPRESS_DIR' ) ) {
	define( 'PRESSABLE_ONEPRESS_DIR', plugin_dir_path( __FILE__ ) );
}

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

// Run the plugin class.
new Pressable_OnePress_Login_Plugin();
