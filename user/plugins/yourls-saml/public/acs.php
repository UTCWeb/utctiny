<?php
/**
 * ACS endpoint (where SAML sends the response)
 */
require(dirname(__DIR__).'/vendor/autoload.php');
require(dirname(__DIR__).'/settings.php');

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

    // Handle the RelayState - determine where to redirect
    $redirect_url = '/';

    if (isset($_POST['RelayState']) && !empty($_POST['RelayState'])) {
        // Use the RelayState provided by SAML if available
        $redirect_url = $_POST['RelayState'];
    } else if (isset($_SESSION['saml_original_url'])) {
        // Use the stored original URL if available
        $redirect_url = $_SESSION['saml_original_url'];
        unset($_SESSION['saml_original_url']);
    }

    // Redirect the user
    header('Location: ' . $redirect_url);
    exit();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
