<?php
/*
Plugin Name: SAML
Plugin URI: https://github.com/wlabarron/yourls-saml
Description: Log in to YOURLS using SAML.
Version: 1.2.2
Author: Andrew Barron
Author URI: https://awmb.uk
*/

// Force output buffering at the earliest possible hook
yourls_add_action('load_template', 'wlabarron_saml_start_buffer', 1);
function wlabarron_saml_start_buffer() {
    if (!yourls_is_API() && !ob_get_level()) {
        ob_start();
    }
}

// Start session as early as possible, before any output
if (!yourls_is_API() && !isset($_SESSION) && php_sapi_name() !== 'cli') {
    // Check if headers were already sent
    if (!headers_sent()) {
        session_start();
    }
}

yourls_add_filter('shunt_is_valid_user', 'wlabarron_saml_authenticate');
function wlabarron_saml_authenticate() {
    if (yourls_is_API()) {
        return false; // Let YOURLS continue with normal API auth
    }

    // Make sure session is started
    if (!isset($_SESSION) && !headers_sent()) {
        session_start();
    }

    require(__DIR__ . '/vendor/autoload.php');
    require(__DIR__ . '/settings.php');

    // Check if this is the ACS endpoint (where SAML sends the response)
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('|/admin/auth/acs\.php$|', $request_uri)) {
        // Let the ACS endpoint handle this - don't redirect
        require_once(__DIR__ . '/public/acs.php');
        return false;
    }

    // If user is already authenticated with SAML, set the YOURLS user and allow access
    if (isset($_SESSION['samlNameId'])) {
        yourls_set_user($_SESSION['samlNameId']);
        return true;
    }

    // Special handling for SAML endpoints
    if (isset($_GET['saml_sso']) || isset($_GET['saml_logout'])) {
        // Handle SAML operations directly without redirection
        $auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);
        if (isset($_GET['saml_sso'])) {
            // Check for existing authentication or loop prevention
            if (isset($_SESSION['saml_auth_in_progress'])) {
                // Authentication already in progress - clear flag and continue with YOURLS
                unset($_SESSION['saml_auth_in_progress']);
                return false;
            }

            // Set flag to prevent loops
            $_SESSION['saml_auth_in_progress'] = true;

            // Store the current URL before authentication
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $uri = $_SERVER['REQUEST_URI'];

            // Remove saml_sso parameter if present to avoid redirect loops
            if (strpos($uri, 'saml_sso=1') !== false) {
                $uri = preg_replace('/([?&])saml_sso=1(&|$)/', '$1', $uri);
                // Clean up any trailing & or ?
                $uri = rtrim($uri, '?&');
            }

            $current_url = $protocol . $host . $uri;
            $_SESSION['saml_original_url'] = $current_url;

            // Determine where to redirect after authentication
            $relay_state = '';

            // If accessing the home page, keep it as the relay state
            if ($request_uri === '/' || $request_uri === '') {
                $relay_state = $protocol . $host . '/';
            }
            // If accessing the admin area, keep it as the relay state
            else if (strpos($request_uri, '/admin/') === 0) {
                $relay_state = $protocol . $host . $request_uri;
            }
            // Default to the stored URL
            else {
                $relay_state = $current_url;
            }

            // Initiate SAML login
            $auth->login($relay_state);
            exit; // Stop execution after redirect
        } elseif (isset($_GET['saml_logout'])) {
            if (isset($_SESSION['samlNameId'])) {
                $auth->logout();
                exit; // Stop execution after redirect
            }
        }
    }

    // If not authenticated, redirect to SAML SSO
    // Use a special URL parameter to prevent endless loop
    if (!isset($_GET['saml_sso'])) {
        // Store the current URL for after authentication
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $_SESSION['saml_original_url'] = $current_url;

        // Redirect to login
        yourls_redirect(yourls_site_url() . '/admin/?saml_sso=1', 302);
        exit;
    }

    // If we get here, something went wrong with authentication
    return false;
}

// Remove log out link from "hello" message
yourls_add_filter('logout_link', 'wlabarron_saml_hello_user');
function wlabarron_saml_hello_user() {
    return sprintf( yourls__('Hello <strong>%s</strong>'), YOURLS_USER );
}

// Deny access to Plugins page for any users not listed in the config file
yourls_add_action( 'auth_successful', function() {
    if( yourls_is_admin() ) wlabarron_saml_intercept_admin();
} );
function wlabarron_saml_intercept_admin() {
    // we use this GET param to send up a feedback notice to user
    if ( isset( $_GET['access'] ) && $_GET['access']=='denied' ) {
        yourls_add_notice('Access Denied');
    }

    // Intercept requests for plugin management
    if(isset( $_SERVER['REQUEST_URI'] ) &&
        preg_match('/\/admin\/plugins/', $_SERVER['REQUEST_URI'] ) ) {

        if (!wlabarron_saml_is_user_in_config()) {
            yourls_redirect( yourls_admin_url( '?access=denied' ), 302 );
        }
    }
}

// Hide plugins from navigation if the user isn't defined in the config file
yourls_add_filter( 'admin_links', 'wlabarron_saml_admin_links' );
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
