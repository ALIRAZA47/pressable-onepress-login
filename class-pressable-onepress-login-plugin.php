<?php
/**
 * Pressable_OnePress_Login_Plugin class
 */
final class Pressable_OnePress_Login_Plugin {
	/** Constructor for the class */
	public function __construct() {
		/** Load after plugins have loaded - https://developer.wordpress.org/reference/hooks/plugins_loaded/ */
		if ( is_ready_to_handle_mpcp_login_request() ) {
			add_action( 'plugins_loaded', array( $this, 'handle_server_login_request' ) );

			// Whitelist MPCP hostname for redirecting on errors.
			add_filter( 'allowed_redirect_hosts', array( $this, 'allowed_redirect_hosts' ) );
		}
	}

	/** Function for handling an incoming login request */
	public function handle_server_login_request() {
		// Get the Auth Token from the request.
		// Inbound URL Example: https://pressable.com/wp-login.php?mpcp_token=MS0wZWQ.
		$base64_token = $_REQUEST['mpcp_token'];

		// Base64 Decode the provided token.
		$token_details = base64_decode( $base64_token );

		// Get reference to user_id, token and site_id.
		list( $user_id, $token, $site_id, $user_agent ) = explode( '-', $token_details );

		// Reference to the WP User.
		$user = new WP_User( $user_id );

		// Reference the stored user meta value.
		$user_meta_value = get_user_meta( $user->ID, 'mpcp_auth_token', true );

		// Remove the stored token details from the user meta.
		delete_user_meta( $user->ID, 'mpcp_auth_token' );

		// Verify token is set on user.
		if ( empty( $user_meta_value ) ) {
			error_log( sprintf( 'OnePress Login user meta value (mpcp_auth_token) not found for user (%d), please try again.', $user->ID ) );

			$message = 'User not found, please try logging in again.';

			wp_safe_redirect(
				add_query_arg(
					'one_click_error',
					rawurlencode( $message ),
					sprintf( 'https://my.pressable.com/sites/%d', $site_id )
				)
			);

			exit;
		}

		// Validate expiration time on token.
		$time = time();
		if ( $user_meta_value['exp'] < $time ) {
			error_log( sprintf( 'OnePress Login authentication token has expired (exp_time: %d, time: %s), please try again.', $user_meta_value['exp'], $time ) );

			$message = 'Authentication token has expired, please try again.';

			wp_safe_redirect(
				add_query_arg(
					'one_click_error',
					rawurlencode( $message ),
					sprintf( 'https://my.pressable.com/sites/%d', $site_id )
				)
			);

			exit;
		}

		// Validate user agent is matching.
		if ( md5( $_SERVER['HTTP_USER_AGENT'] ) !== $user_agent ) {
			error_log( sprintf( 'OnePress Login could not validate user agent (%s), please try again.', $_SERVER['HTTP_USER_AGENT'] ) );

			$message = 'Sorry, we could not validate your request user agent, please try again.';

			wp_safe_redirect(
				add_query_arg(
					'one_click_error',
					rawurlencode( $message ),
					sprintf( 'https://my.pressable.com/sites/%d', $site_id )
				)
			);

			exit;
		}

		// Validate URL token with stored token value.
		if ( md5( $token ) !== $user_meta_value['value'] ) {
			error_log( sprintf( 'OnePress Login invalid authentication token provided (%s), please try again.', $token ) );

			$message = 'Invalid authentication token provided, please try again.';

			wp_safe_redirect(
				add_query_arg(
					'one_click_error',
					rawurlencode( $message ),
					sprintf( 'https://my.pressable.com/sites/%d', $site_id )
				)
			);

			exit;
		}

		// Set cookie for user.
		wp_set_auth_cookie( $user->ID );

		// Handle login action.
		do_action( 'wp_login', $user->user_login, $user );

		// Apply login redirect filter.
		$redirect_to = apply_filters( 'login_redirect', get_dashboard_url( $user->ID ), '', $user );

		// Redirect to the user's dashboard url.
		wp_safe_redirect( $redirect_to );

		exit;
	}

	/**
	 * Whitelist hosts that are allowed to be redirected to.
	 *
	 * @param [Array] $hosts allowed.
	 */
	public function allowed_redirect_hosts() {
		$additional_hosts = array(
			'my.pressable.com',
		);

		return $additional_hosts;
	}
}
