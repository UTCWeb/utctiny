<?php
/*
Plugin Name: SAML
Plugin URI: https://github.com/UTCWeb/utctiny
Description: Log in to YOURLS using SAML.
Version: 1.2.3
Author: Andrew Barron, Chris Gilligan
Author URI: https://go.utc.edu
*/

// Global cookie settings to ensure consistent behavior
$cookie_params = [
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // Auto-detect
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None' // For cross-site authentication
];

// Initialize session early but safely
function wlabarron_saml_init_session() {
    global $cookie_params;

    if (!yourls_is_API() && !isset($_SESSION) && php_sapi_name() !== 'cli') {
        if (!headers_sent()) {
            // Set cookie parameters before starting session
            session_set_cookie_params($cookie_params);
            session_start();

            // Force the session cookie to be secure and SameSite=None
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), $_COOKIE[session_name()],
                    [
                        'expires' => 0,
                        'path' => '/',
                        'domain' => '',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'None'
                    ]
                );
            }
        }
    }
}

// Add this as an early filter to start session before any output
yourls_add_action('load_template_login', 'wlabarron_saml_init_session', 5);
yourls_add_action('admin_init', 'wlabarron_saml_init_session', 5);
yourls_add_action('html_head', 'wlabarron_saml_init_session', 5);
yourls_add_action('pre_yourls_require_template', 'wlabarron_saml_init_session', 5);

// Special hook for frontend form
yourls_add_action('pre_html_form', 'wlabarron_saml_check_frontend_auth', 5);
function wlabarron_saml_check_frontend_auth() {
    // Initialize session
    wlabarron_saml_init_session();

    // If we have a valid SAML session, ensure user is set for frontend
    if (isset($_SESSION['samlNameId'])) {
        yourls_set_user($_SESSION['samlNameId']);
    }
}

// Handle both frontend and admin login
yourls_add_filter('shunt_is_valid_user', 'wlabarron_saml_authenticate');
function wlabarron_saml_authenticate() {
    if (yourls_is_API()) {
        return false; // Don't use SAML for API requests
    }

    // Initialize session if not already started
    if (!isset($_SESSION)) {
        wlabarron_saml_init_session();
    }

    // If we have a valid SAML session, use it
    if (isset($_SESSION['samlNameId'])) {
        yourls_set_user($_SESSION['samlNameId']);
        return true;
    }

    // Only attempt SAML auth if we can modify headers
    if (!headers_sent()) {
        require_once(__DIR__ . '/vendor/autoload.php');
        require_once(__DIR__ . '/settings.php');

        if (isset($wlabarron_saml_settings)) {
            $auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);

            // Store the current URL for returning after auth
            $_SESSION['saml_return_to'] = wlabarron_saml_get_current_url();

            // Redirect to SAML login
            $auth->login();
            exit; // Stop execution after SAML redirect
        }
    }

    return false;
}

// Remove log out link from "hello" message
yourls_add_filter('logout_link', 'wlabarron_saml_hello_user');
function wlabarron_saml_hello_user() {
    return sprintf(yourls__('Hello <strong>%s</strong>'), YOURLS_USER);
}

// Deny access to Plugins page for any users not listed in the config file
yourls_add_action('auth_successful', function() {
    if (yourls_is_admin()) wlabarron_saml_intercept_admin();
});
function wlabarron_saml_intercept_admin() {
    // we use this GET param to send up a feedback notice to user
    if (isset($_GET['access']) && $_GET['access'] == 'denied') {
        yourls_add_notice('Access Denied');
    }

    // Intercept requests for plugin management
    if(isset($_SERVER['REQUEST_URI']) &&
        preg_match('/\/admin\/plugins/', $_SERVER['REQUEST_URI'])) {

        if (!wlabarron_saml_is_user_in_config()) {
            yourls_redirect(yourls_admin_url('?access=denied'), 302);
        }
    }
}

// Hide plugins from navigation if the user isn't defined in the config file
yourls_add_filter('admin_links', 'wlabarron_saml_admin_links');
function wlabarron_saml_admin_links($links) {
    if (!wlabarron_saml_is_user_in_config()) {
        unset($links['plugins']);
    }

    return $links;
}

// Check if the currently logged in user is defined in the config file.
function wlabarron_saml_is_user_in_config() {
    global $yourls_user_passwords;
    $users = array_keys($yourls_user_passwords);

    return in_array(YOURLS_USER, $users);
}

// Helper function to get current URL
function wlabarron_saml_get_current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . $host . $uri;
}
