<?php
require __DIR__.'/../vendor/autoload.php';
use EkoApp\OAuth\EkoOAuthClient;

session_start();

const CLIENT_ID = 'peach2';
const CLIENT_SECRET = 'peach2secret';
const REDIRECT_URI = 'http://localhost:8888/eko-oauth-sdk-php/examples/app.php';
const EKO_URI = 'https://tutorial-h1.dev.ekoapp.com';

// Resolve GET parameters
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;
$error = $_GET['error'] ?? null;

// Check for error
if($error){
    echo $error;
    session_unset();
    exit();
}

// 1. Setup EkoOAuthClient, these values must be matched with pre-registered value on Eko
$client = new EkoOAuthClient();
$client->setClientId(CLIENT_ID);
$client->setClientSecret(CLIENT_SECRET);
$client->setRedirectUri(REDIRECT_URI);
$client->setEkoUri(EKO_URI);

try {
    if(empty($code) || empty($state)){
        // 2. If the code is empty, redirect to the authentication endpoint

        // 2.1 Create a state and save it into the session.
        $_SESSION['state'] = $client->createState();

        // 2.2 Create an authentication endpoint url
        $authEndpointUrl = $client->createAuthenticateUrl($_SESSION['state']);

        // 2.3 Redirect to the authentication endpoint,
        // these code will be vary according to your framework
        header('Location: ' . $authEndpointUrl);
        exit();
    }
    else{
        // 3. Retrieve token and get user info

        // 3.1 Validate the state
        $client->validateState($_SESSION['state'], $state);

        // 3.2 Get user info
        $userInfo = $client->requestUserInfoByCode($code);
        var_dump($userInfo);
        // Or retrieve the token and use it to retrieve user info
        // $token = $client->requestToken($code);
        // $token = $client->requestTokenByRefreshToken($token->getRefreshToken());
        // $userInfo = $client->requestUserInfo($token->getAccessToken());

        // In case you need just an id token
        // $token = $client->requestToken($code);
        // $idToken = $token->getIdToken();

        // 3.3 Do the application logic here
        $text = 'Authorization has been granted to ';
        $text .= $userInfo->firstname ?? '';
        $text .= ' ';
        $text .= $userInfo->lastname ?? '';
        $text .= '. ';
        $text .= 'He is a ';
        $text .= $userInfo->position ?? '';
        $text .= '. ';

        echo $text;
        session_unset();
        exit();
    }
}
catch (Exception $e) {
    echo $e->getMessage();
    echo $e->getTraceAsString();
    session_unset();
}

