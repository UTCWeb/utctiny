<?php
/**
 * ACS endpoint (where SAML sends the response)
 */
require(dirname(dirname(__DIR__, 2)).'/includes/load-yourls.php');
require(dirname(__DIR__).'/vendor/autoload.php');
require_once(dirname(__DIR__).'/settings.php');

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clear the authentication in progress flag
if (isset($_SESSION['saml_auth_in_progress'])) {
    unset($_SESSION['saml_auth_in_progress']);
}

// Process the SAML response
try {
    $auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);
    $auth->processResponse();
    $errors = $auth->getErrors();

    if (!empty($errors)) {
        echo '<p>Error when processing SAML Response:</p>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>';
        if ($auth->getLastErrorReason()) {
            echo '<p>Reason: ' . $auth->getLastErrorReason() . '</p>';
        }
        exit();
    }

    if (!$auth->isAuthenticated()) {
        echo "Not authenticated";
        exit();
    }

    // Successfully authenticated
    $_SESSION['samlUserdata'] = $auth->getAttributes();
    $_SESSION['samlNameId'] = $auth->getNameId();
    $_SESSION['samlNameIdFormat'] = $auth->getNameIdFormat();
    $_SESSION['samlSessionIndex'] = $auth->getSessionIndex();

    // Set the user in YOURLS
    yourls_set_user($_SESSION['samlNameId']);

    // Determine where to redirect
    $redirect_url = yourls_site_url(); // Default to home page

    // Check if there's a RelayState from the SAML response
    if (!empty($_POST['RelayState'])) {
        $redirect_url = $_POST['RelayState'];
    }
    // Fall back to the stored original URL
    else if (isset($_SESSION['saml_original_url'])) {
        $redirect_url = $_SESSION['saml_original_url'];
        unset($_SESSION['saml_original_url']); // Clean up
    }

    // Remove any saml_sso parameter to avoid redirect loops
    if (strpos($redirect_url, 'saml_sso=1') !== false) {
        $redirect_url = preg_replace('/([?&])saml_sso=1(&|$)/', '$1', $redirect_url);
        $redirect_url = rtrim($redirect_url, '?&');
    }

    // Redirect the user
    header('Location: ' . $redirect_url);
    exit();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
