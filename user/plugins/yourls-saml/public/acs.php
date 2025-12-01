<?php
/**
 *  SP Assertion Consumer Service Endpoint
 */
require(dirname(__DIR__).'/vendor/autoload.php');
require_once(dirname(__DIR__).'/settings.php');

// Start session if not already started
if (!isset($_SESSION)) {
    // Set secure cookie parameters
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);
    session_start();
}

$auth = new \OneLogin\Saml2\Auth($wlabarron_saml_settings);
$requestID = isset($_SESSION['AuthNRequestID']) ? $_SESSION['AuthNRequestID'] : null;
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

// Store SAML attributes in session
$_SESSION['samlUserdata'] = $auth->getAttributes();
$_SESSION['samlNameId'] = $auth->getNameId();
$_SESSION['samlNameIdFormat'] = $auth->getNameIdFormat();
$_SESSION['samlNameIdNameQualifier'] = $auth->getNameIdNameQualifier();
$_SESSION['samlNameIdSPNameQualifier'] = $auth->getNameIdSPNameQualifier();
$_SESSION['samlSessionIndex'] = $auth->getSessionIndex();
unset($_SESSION['AuthNRequestID']);

// Determine redirect URL
if (isset($_POST['RelayState']) && !empty($_POST['RelayState'])) {
    $redirectTo = $_POST['RelayState'];
} elseif (isset($_SESSION['saml_return_to'])) {
    $redirectTo = $_SESSION['saml_return_to'];
    unset($_SESSION['saml_return_to']);
} else {
    // Default to home URL
    $redirectTo = $wlabarron_saml_yourls_base_url;
}

// Redirect to the appropriate page
$auth->redirectTo($redirectTo);
