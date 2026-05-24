# Extend VIP Inactive Users

This plugin extends the Automattic VIP Inactive Users protection by enforcing inactive-user blocking on every request, including session-based authentication paths used by SSO plugins.

## What it does

- Hooks into `determine_current_user` to re-check the current user's inactive state.
- Detects inactive users via `\Automattic\VIP\Security\InactiveUsers\Inactive_Users::is_considered_inactive()`.
- Clears auth cookies and server-side auth state for inactive users.
- Redirects inactive users to `/login/` with a specific error query parameter.
- Preserves AJAX/REST requests by returning `0` instead of redirecting.
- Replaces the `frmreg_login_error` message for the inactive-SO redirected login flow.

## Installation

1. Place `extend-vip-inactive-users.php` in your WordPress installation's plugin directory.
2. Activate the plugin from the WordPress admin plugins screen.

## Requirements

- WordPress VIP platform should be present.
- The `Automattic\VIP\Security\InactiveUsers\Inactive_Users` class must be available.
- The site should use `/login/` as the login endpoint or update the redirect URL in the plugin.

## Notes

- This plugin is designed for environments where authentication may bypass the normal WordPress `authenticate` filter, such as SSO or OAuth login flows.
- If the login page path changes, update the `home_url( '/login/' )` redirect target in the plugin.

## License

Use and modify as needed for your project.
