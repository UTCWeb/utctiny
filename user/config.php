<?php
/*
 ** Site options
 */

// In your config.php file
if (getenv('PLATFORM_RELATIONSHIPS')) {
    // We're on Platform.sh

    /** MySQL database */
    define( 'YOURLS_DB_USER', getenv('DB_USERNAME') );
    define( 'YOURLS_DB_PASS', getenv('DB_PASSWORD') );
    define( 'YOURLS_DB_NAME', getenv('DB_NAME') );
    define( 'YOURLS_DB_HOST', getenv('DB_HOST') );
    define( 'YOURLS_DB_PREFIX', 'yourls_' );
    define( 'YOURLS_SITE', 'https://' . $_SERVER['HTTP_HOST'] );
    //Temporary test admin account
    $yourls_user_passwords = [
        'admin' => 'phpass:!2y!10!Cp1IegnyK3u1G0orJpFKPuFisyoLK.rMq6Ta8UY.rUdr4hindh0iC' /* Password encrypted by YOURLS */ ,
        // 'admin' => 'yourls'
    ];

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

/** A random secret hash used to encrypt cookies. You don't have to remember it, make it long and complicated
 ** Hint: copy from http://yourls.org/cookie */
define( 'YOURLS_COOKIEKEY', 'Y9Rd_S2B~aLnmsjYA$2Q8dAp@@WK|E_EKHXw0{6N' );

/** URL shortening method: either 36 or 62
 ** 36: generates all lowercase keywords (ie: 13jkm)
 ** 62: generates mixed case keywords (ie: 13jKm or 13JKm)
 ** For more information, see https://yourls.org/urlconvert */
define( 'YOURLS_URL_CONVERT', 36 );

/** Debug mode to output some internal information
 ** Default is false for live site. Enable when coding or before submitting a new issue */
define( 'YOURLS_DEBUG', false );

/**
* Reserved keywords (so that generated URLs won't match them)
* Define here negative, unwanted or potentially misleading keywords.
*/
$yourls_reserved_URL = [
	'porn', 'faggot', 'sex', 'nigger', 'fuck', 'cunt', 'dick',
];

/**
 * QR Code Settings
 *
 */
define("SEAN_QR_SCALE", 12);
define("SEAN_LOGO_SPACE", 9);
define("SEAN_QR_MARGIN", 2);

/*
 ** Personal settings would go after here.
 */
/*
 ** reCAPTCHA V3 Secret Key, moved from frontend/config.php
 */
define("recaptchaV3SecretKey", '6LdAwAspAAAAAEIj4VafriX1ej1sIuRdqbo2tJNv');
