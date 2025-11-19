<?php
/*
Plugin Name: SAML
Plugin URI: https://github.com/UTCWeb/utctiny
Description: Log in to YOURLS using SAML.
Version: 1.2.3
Author: Andrew Barron, Chris Gilligan
Author URI: https://go.utc.edu
*/


// Start session as early as possible, before any possible output
if (!yourls_is_API() && !isset($_SESSION) && php_sapi_name() !== 'cli') {
    // Check if headers were already sent
    if (!headers_sent()) {
        session_start();
    }
}

// Handle both frontend and admin login
yourls_add_action('pre_login_form', 'wlabarron_saml_intercept_frontend_login');
function wlabarron_saml_intercept_frontend_login() {
    if (!yourls_is_API() && !headers_sent()) {
        // Start session if it hasn't been started yet
        if (!isset($_SESSION)) {
            session_start();
        }

        require_once(__DIR__ . '/vendor/autoload.php');
        require_once(__DIR__ . '/settings.php');

        // If settings are available and we can modify headers
        if (isset($wlabarron_saml_settings)) {
            $auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);

            // If not signed in, redirect to SAML login
            if (!isset($_SESSION['samlNameId'])) {
                $auth->login();
                exit; // Stop execution after redirect
            } else {
                // User is already authenticated via SAML
                yourls_set_user($_SESSION['samlNameId']);
                // Skip the login form and redirect to admin
                yourls_redirect(yourls_admin_url(), 302);
                exit;
            }
        }
    }
    // If headers were already sent or settings not available, continue with regular login form
}

// Handle admin area authentication
yourls_add_filter('shunt_is_valid_user', 'wlabarron_saml_authenticate');
function wlabarron_saml_authenticate() {
    if (!yourls_is_API()) { // Don't use SAML for API requests
        // Start session only if it hasn't been started yet and headers haven't been sent
        if (!isset($_SESSION) && !headers_sent()) {
            session_start();
        }

        require_once(__DIR__ . '/vendor/autoload.php');
        require_once(__DIR__ . '/settings.php');

        // Check if we have SAML session
        if (isset($_SESSION['samlNameId'])) {
            yourls_set_user($_SESSION['samlNameId']);
            return true;
        }

        // Only attempt SAML authentication if we can modify headers and settings are available
        if (!headers_sent() && isset($wlabarron_saml_settings)) {
            $auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);

            // If not signed in, redirect to SAML login
            if (!isset($_SESSION['samlNameId'])) {
                $auth->login();
                exit;
            }
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
    if (isset($_SERVER['REQUEST_URI']) &&
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
