<?php
/**
 * Pressable One Click Log In service provided by Pressable, Inc.
 *
 * @package Pressable Control Panel One Click Log In
 */

/*
Plugin Name: Pressable One Click Log In
Plugin URI: https://my.pressable.com
Description: Pressable One Click Log In service provided by Pressable, Inc. and the My Pressable Control Panel.
Author: Pressable
Version: 1.0.0
Author URI: https://my.pressable.com/
License: GPL2
*/

/** Function for handling an incoming login request */
function handle_server_login_request() {
	/** Handle issue with 2FA not picking up login requests */
	set_wp_functionality_constants();

	// Whitelist MPCP hostname.
	add_filter( 'allowed_redirect_hosts', 'allowed_redirect_hosts' );

	// Get the Auth Token from the request.
	// Inbound URL Example: https://pressable.com/wp-login.php?mpcp_token=MS0wZWQ.
	$base64_token = $_REQUEST['mpcp_token'];

	// Base64 Decode the provided token.
	$token_details = base64_decode( $base64_token );

	// Get reference to user_id, token and site_id.
	list( $user_id, $token, $site_id ) = explode( '-', $token_details );

	// Reference to the WP User.
	$user = new WP_User( $user_id );

	// Meta result is returned as an array.
	$user_meta_value = get_user_meta( $user->ID, 'mpcp_auth_token' );

	// Remove the stored token.
	delete_user_meta( $user->ID, 'mpcp_auth_token' );

	// Verify token.
	if ( ( ! isset( $user_meta_value ) ) || count( $user_meta_value ) < 1 || null === $user_meta_value[0] ) {
		$message = 'User not found, please try logging in again.';

		error_log( $message );

		wp_safe_redirect( "https://my.pressable.com/sites/$site_id?one_click_error=$message" );

		exit;
	}

	/** JSON Decode the stored meta value */
	$decoded_user_meta = json_decode( json_encode( $user_meta_value[0], JSON_FORCE_OBJECT ) );

	/** Validate expiration time on token */
	if ( $decoded_user_meta->exp < time() ) {
		$message = 'Authentication token has expired, please try again.';

		error_log( $message );

		wp_safe_redirect( "https://my.pressable.com/sites/$site_id?one_click_error=$message" );

		exit;
	}

	/** Validate token with stored token */
	if ( md5( $token ) !== $decoded_user_meta->value ) {
		$message = 'Invalid authentication token provided, please try again.';

		error_log( $message );

		wp_safe_redirect( "https://my.pressable.com/sites/$site_id?one_click_error=$message" );

		exit;
	}

	wp_set_auth_cookie( $user->ID );

	do_action( 'wp_login', $user->user_login, $user );

	$redirect_to = apply_filters( 'login_redirect', get_dashboard_url( $user->ID ), '', $user );

	/** Redirect to the user's dashboard url. */
	wp_safe_redirect( $redirect_to );

	exit;
}

/**
 * Decide if request should be handled
 *
 * @return bool True if eligible, False if not.
 */
function is_ready_to_handle_login_request() {
	/** Do not handle if WP is installing, or running a cron or handling AJAX request. */
	if ( wp_installing() || wp_doing_cron() || wp_doing_ajax() ) {
		return false;
	}

	/** Do not handle if WPCLI request */
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return false;
	}

	/** Must include the MPCP login path. */
	if ( is_mpcp_login_request() ) {
		return true;
	}

	return false;
}

/** Determine if request is an MPCP login request */
function is_mpcp_login_request() {
	// Inbound URL Example: https://pressable.com/wp-login.php?mpcp_token=MS0wZWQ.
	return 'wp-login.php' === $GLOBALS['pagenow'] && isset( $_REQUEST['mpcp_token'] );
}

/** Load after plugins have loaded - https://developer.wordpress.org/reference/hooks/plugins_loaded/ */
if ( is_ready_to_handle_login_request() ) {
	add_action( 'plugins_loaded', 'handle_server_login_request' );
}

/**
 * Define functionality-related WordPress constants,
 * as some 2FA providers could not find the constants.
 * This was added due to functionlity noticed in testing WP 2FA
 */
function set_wp_functionality_constants() {
	if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
		define( 'AUTOSAVE_INTERVAL', MINUTE_IN_SECONDS );
	}
}

/**
 * Whitelist hosts that are allowed to be redirected to.
 *
 * @param [Array] $hosts allowed.
 */
function allowed_redirect_hosts( $hosts ) {
	$additional_hosts = array(
		'my.pressable.com',
	);

	return array_merge( $hosts, $additional_hosts );
}
