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
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		/** Handle issue with 2FA not picking up login requests */
		set_wp_functionality_constants();

		if ( ! isset( $_REQUEST['mpcp_token'] ) ) {
			raise_pressable_error( __( '<strong>Error</strong>: Pressable token not found.' ) );
		}

		/** Get the auth token out of query params */
		$base64_token = $_REQUEST['mpcp_token'];

		/** Base64 Decode the provided token */
		$token_details = base64_decode( $base64_token );

		/** Split the result
		 * wp_user_id_result-token
		 */
		$split_token = explode( '-', $token_details );

		$user = new WP_User( $split_token[0] );

		if ( ! $user->exists() ) {
			raise_pressable_error( __( '<strong>Error</strong>: User was not found by <a href="https://my.pressable.com/sites">Pressable</a>.' ) );
		}

		$token = $split_token[1];

		/** Meta result is returned as an array */
		$user_meta_value = get_user_meta( $user->ID, 'mpcp_auth_token' );

		/** Remove the stored token */
		update_user_meta( $user->ID, 'mpcp_auth_token', null );

		if ( ( ! isset( $user_meta_value ) ) || count( $user_meta_value ) < 1 || null === $user_meta_value[0] ) {
			raise_pressable_error( __( '<strong>Error</strong>: User to authenticate was not found by <a href="https://my.pressable.com/sites">Pressable</a>.' ) );
		}

		/** JSON Decode the stored meta value */
		$decoded_user_meta = json_decode( json_encode( $user_meta_value[0], JSON_FORCE_OBJECT ) );

		/** Validate expiration time on token */
		if ( $decoded_user_meta->exp < time() ) {
			raise_pressable_error( __( '<strong>Error</strong>: <a href="https://my.pressable.com/sites">Pressable</a> token has expired, please try again.' ) );
		}

		/** Validate token with stored token */
		if ( md5( $token ) !== $decoded_user_meta->value ) {
			raise_pressable_error( __( '<strong>Error</strong>: Invalid <a href="https://my.pressable.com/sites">Pressable</a> token provided.' ) );
		}

		wp_set_current_user( $user->ID, $user->user_login );

		wp_set_auth_cookie( $user->ID );

		do_action( 'wp_login', $user->user_login, $user );

		$redirect_to = apply_filters( 'login_redirect', get_dashboard_url( $user->ID ), '', $user );

		/** Redirect to the user's dashboard url. */
		wp_safe_redirect( $redirect_to );

		exit;
	}
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
	return 'wp-login.php' === $GLOBALS['pagenow'] && isset( $_REQUEST['mpcp_token'] );
}

/** Load after plugins have loaded - https://developer.wordpress.org/reference/hooks/plugins_loaded/ */
if ( is_ready_to_handle_login_request() ) {
	add_action( 'plugins_loaded', 'handle_server_login_request' );
}

/**
 * Render an error response
 *
 * @param string $message Message to be posted.
 * */
function raise_pressable_error( string $message ) {
	wp_die( $message );

	exit;
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
