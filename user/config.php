<?php
/*
 ** Site options
 */
use Platformsh\ConfigReader\Config;
$platformsh = new Config();

if (getenv('PLATFORM_RELATIONSHIPS')) {
    // We're on Platform.sh
    /** MySQL database */
    define( 'YOURLS_DB_USER', getenv('DB_USERNAME') );
    define( 'YOURLS_DB_PASS', getenv('DB_PASSWORD') );
    define( 'YOURLS_DB_NAME', getenv('DB_NAME') );
    define( 'YOURLS_DB_HOST', getenv('DB_HOST') );
    define( 'YOURLS_DB_PREFIX', 'yourls_' );
    define( 'YOURLS_SITE', getenv('APP_URL') );
    /** Username(s) utc123 allowed to access the Admin and Plugins.
     ** This ONLY applies if SAML plugin is disabled.
     ** Read http://yourls.org/userpassword for more information */
    $admin_password_from_env = getenv('UTC123') ?: '';
    $yourls_user_passwords = [
        'utc123' => $admin_password_from_env,
        // You can have one or more 'login'=>'password' lines
        // Also: MUST configure user level in Auth Manager Plus section
    ];
    /*
     ** Auth Manager Plus plugin configuration
     ** Role Assignments per user
     */
    $amp_default_role = "anonymous";
    $amp_role_assignment = array(
        'administrator' => array(
            'utc123',/* UC */
            'xjm218',/* BH */
            'jty711',/* CG */
            'cpg381',/* SC */
            'xpn146',/* WG */
        ),
        'editor' => array(
            'ckg289',/* SS */
            'hmb868',/* AC */
        ),
        'contributor' => array(
            'xjg733',/* TC */
            'pqb796',/* BR */
            'qtx683',/* NW */
            'vld282',/* CC */
        ),
    );

    define("recaptchaV3SecretKey", getenv('recaptchaV3SecretKey') );
    define( 'YOURLS_COOKIEKEY', getenv('YOURLS_COOKIEKEY') );

} else {
    // Local DDev development
    define( 'YOURLS_SITE', 'https://utctiny.ddev.site' );
    define( 'YOURLS_DB_USER', 'db' );
    define( 'YOURLS_DB_PASS', 'db' );
    define( 'YOURLS_DB_NAME', 'db' );
    define( 'YOURLS_DB_HOST', 'db' );
    define( 'YOURLS_DB_PREFIX', 'yourls_' );
    $yourls_user_passwords = [
        'admin' => 'phpass:!2y!10!Cp1IegnyK3u1G0orJpFKPuFisyoLK.rMq6Ta8UY.rUdr4hindh0iC' /* Password encrypted by YOURLS */ ,
        // 'admin' => 'yourls'
    ];
    /** reCAPTCHA V3 Secret Key, used only for utctiny.ddev.site */
    define("recaptchaV3SecretKey", '6LdAwAspAAAAAEIj4VafriX1ej1sIuRdqbo2tJNv');
    /** A hash used to encrypt cookies. This one is used only local dev
     ** Hint: copy from http://yourls.org/cookie */
    define( 'YOURLS_COOKIEKEY', 'YOURLS_LOCAL_COOKIEKEY' );
}

// ---------- Environment-aware YOURLS_DEBUG ----------

// Detect environments
$platformEnv = getenv('PLATFORM_ENVIRONMENT') ?: '';
$isDdev      = getenv('IS_DDEV_PROJECT') === 'true';

// Decide whether debug should be enabled:
//
// - DDEV local: always true
// - Platform.sh: true for all environments except 'main'
// - Other environments (no PLATFORM_ENVIRONMENT & not DDEV): you can choose;
//   here we default to false for safety.
if ($isDdev) {
    $debugEnabled = true;
} elseif ($platformEnv !== '') {
    // On Platform.sh
    $debugEnabled = ($platformEnv !== 'main');
} else {
    // Other hosting
    $debugEnabled = false;
}

// Make this file authoritative about YOURLS_DEBUG
if (defined('YOURLS_DEBUG')) {
    error_log('YOURLS: YOURLS_DEBUG was already defined, overriding in user/config.php');
}
define('YOURLS_DEBUG', $debugEnabled);

// Apply PHP error settings consistent with YOURLS_DEBUG
if (YOURLS_DEBUG) {
    error_log("YOURLS: debug ENABLED (platformEnv='$platformEnv', isDdev=" . ($isDdev ? 'true' : 'false') . ')');
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    error_log("YOURLS: debug DISABLED (platformEnv='$platformEnv', isDdev=" . ($isDdev ? 'true' : 'false') . ')');
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

/** YOURLS language
 ** Change this setting to use a translation file for your language, instead of the default English.
 ** That translation file (a .mo file) must be installed in the user/language directory.
 ** See http://yourls.org/translations for more information */

define( 'YOURLS_LANG', '' );

/** Allow multiple short URLs for a same long URL
 ** Set to true to have only one pair of shortURL/longURL (default YOURLS behavior)
 ** Set to false to allow multiple short URLs pointing to the same long URL (bit.ly behavior) */
define( 'YOURLS_UNIQUE_URLS', true );

/** Private means the Admin area will be protected with login/pass as defined below.
 ** Set to false for public usage (eg on a restricted intranet or for test setups)
 ** Read http://yourls.org/privatepublic for more details if you're unsure */
define( 'YOURLS_PRIVATE', true );

/** URL shortening method: either 36 or 62
 ** 36: generates all lowercase keywords (ie: 13jkm)
 ** 62: generates mixed case keywords (ie: 13jKm or 13JKm)
 ** For more information, see https://yourls.org/urlconvert */
define( 'YOURLS_URL_CONVERT', 36 );

/**
* Reserved keywords (so that generated URLs won't match them)
* Define here negative, unwanted or potentially misleading keywords.
*/
$yourls_reserved_URL = [
	'porn', 'faggot', 'sex', 'nigger', 'fuck', 'cunt', 'dick',
];

/**
 * QR Code Settings
 */
define("SEAN_QR_SCALE", 12);
define("SEAN_LOGO_SPACE", 9);
define("SEAN_QR_MARGIN", 2);
