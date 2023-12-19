<?php
/*
Plugin Name: YOURLS Favicon
Plugin URI: https://github.com/yourls/yourls-favicon
Description: Displays a fancy YOURLS favicon in all flavors (Chrome, Safari, Android...)
Version: 1.0.1
Author: Ozh/UTCGilligan
Author URI: https://github.com/UTCWeb/utctiny
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_filter('shunt_html_favicon', 'yourls_plugin_favicon');

function yourls_plugin_favicon() {
    $url = yourls_plugin_url(__DIR__).'/assets';
    $ver = YOURLS_VERSION;

    echo <<<HTML

    <link rel="apple-touch-icon" sizes="180x180" href="$url/apple-touch-icon.png?v=$ver">
    <link rel="icon" type="image/svg+xml" href="$url/favicon.svg?v=$ver">
    <link rel="icon" type="image/png" href="$url/favicon.png?v=$ver">
    <link rel="icon" type="image/png" sizes="32x32" href="$url/favicon-32x32.png?v=$ver">
    <link rel="icon" type="image/png" sizes="16x16" href="$url/favicon-16x16.png?v=$ver">
    <link rel="manifest" href="$url/site.webmanifest?v=$ver">
    <link rel="mask-icon" href="$url/favicon.svg?v=$ver" color="#112e51">
    <link rel="shortcut icon" href="$url/favicon.ico?v=$ver">
    <meta name="msapplication-TileColor" content="#112e51">
    <meta name="msapplication-TileImage" content="$url/mstile-144x144.png?v=$ver">
    <meta name="msapplication-config" content="$url/browserconfig.xml?v=$ver">
    <meta name="theme-color" content="#112e51">

HTML;

    return true;
}