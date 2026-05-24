<?php
/**
 * Extend the VIP Inactive Users block to cover all authentication paths.
 *
 * The platform's Inactive Users module enforces its block at the WordPress
 * authenticate filter, which SSO plugins (Miniorange OAuth, etc.) bypass by
 * establishing sessions directly. This filter re-checks inactive status on
 * every request, covering all auth paths.
 */

add_filter( 'determine_current_user', function( $user_id ) {
    // Prevent recursion and short-circuit if there is no current user.
    static $running = false;
    if ( $running || ! $user_id ) {
        return $user_id;
    }

    // Do nothing if the Inactive Users class is not available.
    if ( ! class_exists( '\Automattic\VIP\Security\InactiveUsers\Inactive_Users' ) ) {
        return $user_id;
    }

    // Only take action for users flagged as inactive.
    if ( ! \Automattic\VIP\Security\InactiveUsers\Inactive_Users::is_considered_inactive( $user_id ) ) {
        return $user_id;
    }

    // Mark this callback as running so we do not re-enter it recursively.
    $running = true;

    // Remove auth cookies from the request so the user is effectively logged out.
    foreach ( [ AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE ] as $cookie_name ) {
        unset( $_COOKIE[ $cookie_name ] );
    }

    // Clear server-side authentication state.
    wp_clear_auth_cookie();

    // For AJAX or REST requests, return zero to indicate no current user.
    if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return 0;
    }

    // Redirect the user to the login page with a specific inactive-account error.
    wp_safe_redirect(
        add_query_arg(
            [ 'frmreg_error' => 'inactive_sso', 'blocked' => '1' ],
            home_url( '/login/' )
        )
    );
    exit;
}, 30 );

add_filter( 'frmreg_login_error', function( $message ) {
    // Only override the login error message for the inactive SSO redirect.
    if ( ! isset( $_GET['frmreg_error'] ) || $_GET['frmreg_error'] !== 'inactive_sso' ) {
        return $message;
    }

    return esc_html__( 'Your account has been flagged as inactive. Please contact VIP Learning and Credentialing to regain access.', 'valet' );
} );
