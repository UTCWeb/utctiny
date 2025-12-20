<?php
// CONFIG - These control the look and details on your site. Consult documentation for more details.

// GENERAL

// Page title for your site
if (!defined('title')) {
    define('title', 'UTC URL Shortener');
}

// The short title of your site, used in the footer and in some sub pages
if (!defined('shortTitle')) {
    define('shortTitle', 'go.UTC.edu');
}

// A description of your site, shown on the homepage.
if (!defined('description')) {
    define('description', '<strong>Long URLs annoy.</strong> Shorten and share, Mocs.<br /><small><em>Short links and URLs entered at go.UTC.edu are actively monitored by UTC IT.</em></small>');
}

// The favicon for your site
if (!defined('favicon')) {
    define('favicon', 'user/plugins/yourls-favicon/assets/favicon.svg');
}

// Logo for your site, displayed on home page
if (!defined('logo')) {
    define('logo', '/frontend/assets/svg/utc-wordmark-reverse.svg');
}

// Enable reCAPTCHA V3
// It is highly recommended you use reCAPTCHA V3. It will stop spam. You can get a site and secret key from here: https://www.google.com/recaptcha/admin/create
if (!defined('enableRecaptcha')) {
    define('enableRecaptcha', false);
}

// reCAPTCHA V3 Site Key
if (!defined('recaptchaV3SiteKey')) {
    define('recaptchaV3SiteKey', '6LfgoQssAAAAAB_UqQtwNSCcsUab6v-EXAuykF4U');
}

// reCAPTCHA V3 Secret Key
//define("recaptchaV3SecretKey", 'moved to app config for secrecy');

// Enables the custom URL field
// true or false
if (!defined('enableCustomURL')) {
    define('enableCustomURL', true);
}

// Optional
// Set a primary colour to be used. Default: #007bff
// Here are some other colours you could try:
// #f44336: red, #9c27b0: purple, #00bcd4: teal, #ff5722: orange
if (!defined('colour')) {
    define('colour', '#112E51');
}

// Optional
// Set a background image to be used.
// default: unsplash.com random daily photo of the day
// More possibilities of photo embedding from unsplash could be found at: https://source.unsplash.com
// define('backgroundImage', 'https://source.unsplash.com/daily');

// FOOTER

// These are the links in the footer. Add a new link for each new link.
// The array follows a title link structure:
// "TITLE" => "LINK",
if (!isset($footerLinks) || !is_array($footerLinks)) {
    $footerLinks = [
        "About"   => "https://utc.teamdynamix.com/TDClient/2717/Portal/Requests/ServiceDet?ID=50665",
        "Privacy" => "https://www.utc.edu/about/privacy",
        "Manage My Links"   =>  "/admin/",
    ];
}
