<?php
/*
Plugin Name: SAML
Plugin URI: https://github.com/wlabarron/yourls-saml
Description: Log in to YOURLS using SAML.
Version: 1.2.1
Author: Andrew Barron
Author URI: https://awmb.uk
Edited by: CCG and PHPStorm AI GPT-5.1
*/

yourls_add_filter('shunt_is_valid_user', 'wlabarron_saml_authenticate');

function wlabarron_saml_authenticate()
{
    if (!yourls_is_API()) { // Don't use SAML for API requests
        // Start session safely (avoid notices if already active)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        require __DIR__ . '/vendor/autoload.php';
        require __DIR__ . '/settings.php';

        // Ensure we see the settings defined in settings.php
        if (!isset($wlabarron_saml_settings) || !is_array($wlabarron_saml_settings)) {
            // You could log or handle this more gracefully if desired
            return false;
        }

        $auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);

        // If not signed in, sign in
        if (!isset($_SESSION['samlNameId'])) {
            $auth->login();
        }

        yourls_set_user($_SESSION['samlNameId']);

        return isset($_SESSION['samlNameId']);
    }

    // For API requests we don’t handle auth here
    return null;
}
