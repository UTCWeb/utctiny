<?php
// Special handling for frontend authentication
// This file should be included in the plugin.php file

// Add a hook to intercept the loader workflow
yourls_add_action('loader_failed', 'wlabarron_saml_handle_root_url');

function wlabarron_saml_handle_root_url($request) {
    // Only process empty requests (root URL)
    if ($request !== '') {
        return;
    }

    // Initialize session
    wlabarron_saml_init_session();

    // If user is authenticated via SAML, show the frontend form
    if (isset($_SESSION['samlNameId'])) {
        // Set the user for YOURLS
        yourls_set_user($_SESSION['samlNameId']);

        // Force display of the frontend form for logged in users
        include_once(YOURLS_ABSPATH . '/includes/functions-html.php');
        yourls_html_head('new');
        yourls_html_logo();
        yourls_html_menu();
        yourls_html_form();
        yourls_html_footer();
        exit;
    }
}

// Add a hook to handle the frontend form template
yourls_add_action('pre_yourls_require_template', 'wlabarron_saml_check_template');

function wlabarron_saml_check_template($template) {
    // Initialize session
    wlabarron_saml_init_session();

    // If this is the root URL or index.php and SAML auth is active
    if ($template === 'index.php' && isset($_SESSION['samlNameId'])) {
        yourls_set_user($_SESSION['samlNameId']);
    }
}
