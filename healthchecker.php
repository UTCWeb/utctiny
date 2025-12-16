<?php
// Define YOURLS_HO_HUM_HEALTHCHECK to prevent YOURLS from doing its usual boot sequence for the admin panel
define('YOURLS_HO_HUM_HEALTHCHECK', true);

// Include the main YOURLS bootstrapper
require_once( dirname(__FILE__) . '/yourls-loader.php' ); // Adjust path as necessary

header("Content-Type: text/plain");

// Check database connection
if (yourls_db_connect()) {
    http_response_code(200);
    echo "OK - Database Connected";
} else {
    http_response_code(503); // Service Unavailable
    echo "Error - Database Connection Failed";
}
?>
