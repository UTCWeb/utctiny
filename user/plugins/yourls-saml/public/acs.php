<?php

/**
 *  SP Assertion Consumer Service Endpoint
 */

session_start();

require(dirname(__DIR__).'/vendor/autoload.php');
require_once(dirname(__DIR__).'/settings.php');
$auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);

if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
    $requestID = $_SESSION['AuthNRequestID'];
} else {
    $requestID = null;
}

$auth->processResponse($requestID);

$errors = $auth->getErrors();

if (!empty($errors)) {
    echo '<p>',implode(', ', $errors),'</p>';
    if ($auth->getSettings()->isDebugActive()) {
        echo '<p>'.$auth->getLastErrorReason().'</p>';
    }
}

if (!$auth->isAuthenticated()) {
    echo "<p>Not authenticated</p>";
    exit();
}

$_SESSION['samlUserdata'] = $auth->getAttributes();
$_SESSION['samlNameId'] = $auth->getNameId();
$_SESSION['samlNameIdFormat'] = $auth->getNameIdFormat();
$_SESSION['samlNameIdNameQualifier'] = $auth->getNameIdNameQualifier();
$_SESSION['samlNameIdSPNameQualifier'] = $auth->getNameIdSPNameQualifier();
$_SESSION['samlSessionIndex'] = $auth->getSessionIndex();
unset($_SESSION['AuthNRequestID']);

// Determine where to redirect after successful authentication
if (isset($_POST['RelayState']) && !empty($_POST['RelayState'])) {
    $redirectTo = $_POST['RelayState'];
} elseif (isset($_SESSION['saml_return_to'])) {
    $redirectTo = $_SESSION['saml_return_to'];
    unset($_SESSION['saml_return_to']);
} else {
    // Default to admin URL
    $redirectTo = rtrim($wlabarron_saml_yourls_base_url, '/') . '/admin/';
}

// Redirect to the appropriate page
$auth->redirectTo($redirectTo);
