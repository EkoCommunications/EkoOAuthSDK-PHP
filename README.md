# EkoOAuthSDK-PHP

An OAuth authentication client for integrating 3rd party application with Eko App.


### Prerequisites

Client application must be registered with Eko first. These values, `redirect_uri`, `client_id`, `client_secret` and `eko_uri`, will be defined during registration process.


### Running Example

1. Edit the `client_id`, `client_secret`, `redirect_uri` and `eko_uri` in `/examples/app.php`.
2. Go to `http://localhost/path/to/project/examples/app.php` via the browser

### Installation
```bash
composer require ekoapp/eko-oauth-sdk
```

### Usage

See `examples/app.php` for the complete flow of usage.

#### 1. Initialization
```php
use EkoApp\OAuth\EkoOAuthClient;

$client = new EkoOAuthClient();
$client->setClientId(CLIENT_ID);
$client->setClientSecret(CLIENT_SECRET);
$client->setRedirectUri(REDIRECT_URI);
$client->setEkoUri(EKO_URI);
```


#### 2. Authentication
To authenticate a user, the client application must redirect the user to the url below.

- Create a redirect url to Eko authentication endpoint

```php
$_SESSION['state'] = $client->createState();
// DO NOT forget to store this state into the session to validate it when Eko redirect back to your endpoint

$authEndpointUrl = $client->createAuthenticateUrl($_SESSION['state']);
```


#### 3. Get token and user info
The client application must setup an endpoint which must match the predefined `redirect_uri`. After authentication success or fail, Eko will redirect the user back to this `redirect_uri` endpoint along with `state` and `code`  (authentication code) as query parameters. The client application must validate the incoming state with the one previously store on the session. Then, the client application use the `code` to retrieve access token and use the access token to retrieve user info. DO NOT use the `code` if state validation fail.


- Validate state (if fail, exceptions will be thrown)
```php
$client->validateState($_SESSION['state'], $state);
```


- Get token
```php
$token = $client->requestToken($code);
```
```php
// Retreiving values from access token
$accessToken = $token->getAccessToken();
$refreshToken = $token->getRefreshToken();
$tokenType = $token->getTokenType();
$expiresIn = $token->getExpiresIn();
$scopes = $token->getScopes();
$rawIdToken = $token->getRawIdToken();
$idToken = $token->getIdToken();
```


- Get ID token
```php
$idToken = $token->getIdToken();
```
```php
// Retreiving values from id token
$firstName = $idToken->firstname;
$lastName = $idToken->lastname;
$email = $idToken->email;
```


- Get user info
```php
$userInfo = $client->requestUserInfo($token->getAccessToken());
```
```php
// Retreiving values from user info
$userId = $userInfo->_id;
$firstName = $userInfo->firstname;
$lastName = $userInfo->lastname;
$email = $userInfo->email;
$networkId = $userInfo->networkId;
```


or a shortcut to retreive user info ...
- Get user info by `code`
```php
$userInfo = $client->requestUserInfoByCode($code);
```

#### 4. Refresh Token

```java
$token = $client->requestTokenByRefreshToken($token->getRefreshToken());
```

## Release

| Version | Changes |
| ------ | ------ |
| 1.0.0 | <ul> <li>Initial release</li> </ul> |

## Authors

* **Jura Boonnom** - *Initial work* - [jura-b](https://github.com/jura-b)

