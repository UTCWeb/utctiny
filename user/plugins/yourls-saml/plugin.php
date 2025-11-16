<?php
/*
Plugin Name: SAML
Plugin URI: https://github.com/wlabarron/yourls-saml
Description: Log in to YOURLS using SAML.
Version: 1.2utc
Author: Andrew Barron
Author URI: https://awmb.uk
*/

// Initialize the session as early as possible
yourls_add_action('init', 'wlabarron_saml_init');
function wlabarron_saml_init() {
    if (!yourls_is_API() && !isset($_SESSION)) {
        session_start();
    }
}

yourls_add_filter('shunt_is_valid_user', 'wlabarron_saml_authenticate');
function wlabarron_saml_authenticate() {
    if (!yourls_is_API()) { // Don't use SAML for API requests
        // Session should be already started by this point
        require(__DIR__ . '/vendor/autoload.php');
        require(__DIR__ . '/settings.php');
        $auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);

        // If not signed in, sign in
        if (!isset($_SESSION['samlNameId'])) $auth->login();

        yourls_set_user($_SESSION['samlNameId']);
        return isset($_SESSION['samlNameId']);
    }
}

// Rest of your plugin code remains unchanged...
