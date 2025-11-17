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

// Clear any auth in progress flag
if (isset($_SESSION['saml_auth_in_progress'])) {
    unset($_SESSION['saml_auth_in_progress']);
}

if (isset($_POST['RelayState']) && \OneLogin\Saml2\Utils::getSelfURL() != $_POST['RelayState']) {
    // If RelayState contains /admin/ and we want to go to root, strip it
    if (strpos($_POST['RelayState'], '/admin/') !== false && !isset($_GET['admin_requested'])) {
        $redirect_url = str_replace('/admin/', '/', $_POST['RelayState']);
        $redirect_url = preg_replace('/[?&]saml_sso=1(&|$)/', '', $redirect_url);
        $auth->redirectTo($redirect_url);
    } else {
        // Normal RelayState handling
        $auth->redirectTo($_POST['RelayState']);
    }
} else {
    // Default to home page if no RelayState
    $auth->redirectTo("/");
}
