<?php
/*
Plugin Name: SAML
Plugin URI: https://github.com/UTCWeb/utctiny
Description: Log in to YOURLS using SAML.
Version: 1.2.3
Author: Andrew Barron, Chris Gilligan
Author URI: https://go.utc.edu
*/

// Very early session management
// This needs to happen before any output is sent
function wlabarron_saml_init_session() {
    if (yourls_is_API() || isset($_SESSION) || php_sapi_name() === 'cli' || headers_sent()) {
        return; // Skip if not needed or not possible
    }

    // Set cookie parameters before starting session
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);

    session_start();
}

// Register this function as early as possible with various hooks
yourls_add_action('pre_yourls_require_template', 'wlabarron_saml_init_session', 1);
yourls_add_action('load_template_login', 'wlabarron_saml_init_session', 1);
yourls_add_action('admin_init', 'wlabarron_saml_init_session', 1);
yourls_add_action('html_head', 'wlabarron_saml_init_session', 1);
yourls_add_action('pre_html_form', 'wlabarron_saml_init_session', 1);
yourls_add_action('pre_yourls_serve_request', 'wlabarron_saml_init_session', 1);
yourls_add_action('load_template_redirect_admin', 'wlabarron_saml_init_session', 1);
yourls_add_action('loader_failed', 'wlabarron_saml_init_session', 1);

// Add JavaScript to modify frontend links in admin area
yourls_add_action('admin_init', 'wlabarron_saml_add_frontend_js');
function wlabarron_saml_add_frontend_js() {
    // Only add the JS if we're in admin and have a valid SAML session
    if (!yourls_is_admin() || !isset($_SESSION['samlNameId'])) {
        return;
    }

    // Add script to modify frontend links
    echo '<script>var yourls_site = "' . YOURLS_SITE . '";</script>';
    echo '<script src="' . yourls_plugin_url('yourls-saml/saml-frontend.js') . '"></script>';
}

// Replace the standard frontend link with one that maintains session
yourls_add_filter('admin_menu', 'wlabarron_saml_filter_admin_menu');
function wlabarron_saml_filter_admin_menu($links) {
    // If we have a valid SAML session, replace the frontend link
    if (isset($_SESSION['samlNameId'])) {
        // Get the base URL (without trailing slash)
        $site = rtrim(YOURLS_SITE, '/');

        // Replace the frontend link with our special one
        $links['frontend'] = '<a href="' . $site . '/?auth_maintain=1">' . yourls__('Frontend form') . '</a>';
    }

    return $links;
}

// Handle special redirect from ACS.php
yourls_add_action('admin_init', 'wlabarron_saml_handle_auth_redirect');
function wlabarron_saml_handle_auth_redirect() {
    // Check if this is a special redirect request
    if (isset($_GET['action']) && $_GET['action'] === 'auth_redirect' && isset($_GET['dest'])) {
        // Ensure session is established
        wlabarron_saml_init_session();

        // Verify we have a valid SAML session
        if (!isset($_SESSION['samlNameId'])) {
            return; // Not authenticated, let normal flow continue
        }

        // Set user for YOURLS
        yourls_set_user($_SESSION['samlNameId']);

        // Redirect to destination
        $destination = $_GET['dest'];
        yourls_redirect($destination);
        exit;
    }

    // Also check for final_redirect from session
    if (isset($_SESSION['final_redirect'])) {
        $finalRedirect = $_SESSION['final_redirect'];
        unset($_SESSION['final_redirect']);
        yourls_redirect($finalRedirect);
        exit;
    }
}

// Main authentication function
yourls_add_filter('shunt_is_valid_user', 'wlabarron_saml_authenticate');
function wlabarron_saml_authenticate() {
    if (yourls_is_API()) {
        return false; // Don't use SAML for API requests
    }

    // If we have a valid SAML session, use it
    if (isset($_SESSION) && isset($_SESSION['samlNameId'])) {
        yourls_set_user($_SESSION['samlNameId']);
        return true;
    }

    // Only attempt SAML auth if we can modify headers
    if (!headers_sent()) {
        require_once(__DIR__ . '/vendor/autoload.php');
        require_once(__DIR__ . '/settings.php');

        if (isset($wlabarron_saml_settings)) {
            $auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);

            // Make sure we have a session started
            if (!isset($_SESSION)) {
                wlabarron_saml_init_session();
            }

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

// This is the key function for frontend authentication
// We hook into the 'loader_failed' action, which is triggered when
// a request doesn't match any known pattern in yourls-loader.php
yourls_add_action('loader_failed', 'wlabarron_saml_handle_root_url');
function wlabarron_saml_handle_root_url($request) {
    // Initialize session
    wlabarron_saml_init_session();

    // Check if this is the root URL (empty request)
    if ($request === '') {
        // Check for our special parameter that indicates we're coming from admin area
        if (isset($_GET['auth_maintain']) && $_GET['auth_maintain'] === '1') {
            // If we have a valid SAML session, show the frontend form
            if (isset($_SESSION['samlNameId'])) {
                yourls_set_user($_SESSION['samlNameId']);

                // Display the frontend form
                include_once(YOURLS_ABSPATH . '/includes/functions-html.php');
                yourls_html_head('new');
                yourls_html_logo();
                yourls_html_menu();
                yourls_html_form();
                yourls_html_footer();
                exit;
            }
        }
        // Also check for regular authenticated users
        elseif (isset($_SESSION['samlNameId'])) {
            yourls_set_user($_SESSION['samlNameId']);

            // Display the frontend form
            include_once(YOURLS_ABSPATH . '/includes/functions-html.php');
            yourls_html_head('new');
            yourls_html_logo();
            yourls_html_menu();
            yourls_html_form();
            yourls_html_footer();
            exit;
        }
    }
}

// Add hook to check and enforce authentication for the form
yourls_add_action('html_form', 'wlabarron_saml_ensure_frontend_auth', 1);
function wlabarron_saml_ensure_frontend_auth() {
    // If we're displaying the form and have a SAML session, ensure user is set
    if (isset($_SESSION['samlNameId'])) {
        yourls_set_user($_SESSION['samlNameId']);
    }
}

// This hook runs very early in the YOURLS initialization
// It allows us to capture and handle requests before normal processing
yourls_add_action('pre_yourls_serve_request', 'wlabarron_saml_check_auth_maintain');
function wlabarron_saml_check_auth_maintain() {
    // Check if we have the special auth_maintain parameter
    if (isset($_GET['auth_maintain']) && $_GET['auth_maintain'] === '1') {
        // Initialize session
        wlabarron_saml_init_session();

        // If we have a valid SAML session, make sure it's used
        if (isset($_SESSION['samlNameId'])) {
            yourls_set_user($_SESSION['samlNameId']);
        }
    }
}
