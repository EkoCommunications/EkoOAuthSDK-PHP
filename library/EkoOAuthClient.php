<?php
/**
 * Created by PhpStorm.
 * User: juraboonnom
 * Date: 12/14/2017 AD
 * Time: 3:22 PM
 */
namespace EkoApp\OAuth;

use EkoApp\OAuth\Exceptions\ClientException;
use EkoApp\OAuth\Exceptions\InvalidStateException;

/**
 * Class EkoOAuthClient
 * @package EkoApp\OAuth
 */
class EkoOAuthClient
{
    /**
    * @var string|null client id
    */
    private $clientId;

    /**
     * @var string|null client secret
     */
    private $clientSecret;

    /**
     * @var string|null redirect uri of client application
     */
    private $redirectUri;

    /**
     * @var string|null token uri of EkoApp
     */
    private $tokenUri;

    /**
     * @var string|null user info of EkoApp
     */
    private $userInfoUri;

    /**
     * @var string|null authentication uri of EkoApp
     */
    private $authenticateUri;

    /**
     * @var string|null base uri of EkoApp
     */
    private $ekoUri;

    /**
     * @var string|null scope
     */
    private $scope;

    public function __construct()
    {
        $this->scope = 'openid profile';
    }

    /**
     * Request a token by authentication code
     * @param $code
     * @return \EkoApp\OAuth\OAuthToken
     * @throws ClientException
     */
    public function requestToken($code)
    {
        $response = \Requests::post($this->getTokenUri(), [
            'Authorization' => $this->createBasicCredentialString()
        ], [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri()
        ]);

        if ($response->success) {
            return new OAuthToken($response->body, $this->getClientSecret(), $this->getAuthenticateUri());
        } else {
            throw new ClientException($response->body, $response->status_code);
        }
    }

    /**
     * Request a token by refresh token
     * @param $refreshToken
     * @return \EkoApp\OAuth\OAuthToken
     * @throws ClientException
     */
    public function requestTokenByRefreshToken($refreshToken)
    {
        $response = \Requests::post($this->getTokenUri(), [
            'Authorization' => $this->createBasicCredentialString()
        ], [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'redirect_uri' => $this->getRedirectUri()
        ]);

        if ($response->success) {
            return new OAuthToken($response->body, $this->getClientSecret(), $this->getAuthenticateUri());
        } else {
            throw new ClientException($response->body, $response->status_code);
        }
    }

    /**
     * Request user information by access token
     * @param $accessToken
     * @return \stdClass
     * @throws ClientException
     */
    public function requestUserInfo($accessToken)
    {
        $response = \Requests::get($this->getUserInfoUri(), [
            'Authorization' => $this->createBearerCredentialString($accessToken)
        ]);

        if ($response->success) {
            return json_decode($response->body);
        } else {
            throw new ClientException($response->body, $response->status_code);
        }
    }

    /**
     * Request user information by authentication code
     * @param $code
     * @return \stdClass
     * @throws ClientException
     */
    public function requestUserInfoByCode($code)
    {
        $token = $this->requestToken($code);

        $response = \Requests::get($this->getUserInfoUri(), [
            'Authorization' => $this->createBearerCredentialString($token->getAccessToken())
        ]);

        if ($response->success) {
            return json_decode($response->body);
        } else {
            throw new ClientException($response->body, $response->status_code);
        }
    }

    /**
     * Create a random state string
     * @throws \Exception
     * @return string
     */
    public function createState()
    {
        $charset = array_merge(range(0, 9), range('A', 'Z'), range('a', 'z'));
        $min = 0;
        $max = count($charset) - 1;
        $length = 32;

        $state = '';
        for($i = 0; $i < $length; $i++) {
            $state .= $charset[random_int($min, $max)];
        }
        return $state;
    }


    /**
     * Create an authentication url according to preset parameters
     * @return string
     */
    public function createAuthenticateUrl($state)
    {
        return $this->getAuthenticateUri() .
            '?response_type=code' .
            '&client_id=' . $this->getClientId() .
            '&redirect_uri=' . $this->getRedirectUri() .
            '&scope=' . $this->getScope() .
            '&state=' . $state;
    }

    /**
     * Create a basic credential string according to preset parameters
     * This string will be used to attach to a request's authorization header
     * @return string
     */
    private function createBasicCredentialString()
    {
        $credential = $this->getClientId() . ':' . $this->getClientSecret();
        return 'Basic ' . base64_encode($credential);
    }

    /**
     * Create a bearer credential string
     * This string will be used to attach to a request's authorization header
     * @param string $accessToken access token string
     * @return string
     */
    private function createBearerCredentialString($accessToken)
    {
        $credential = 'Bearer ' . $accessToken;
        return $credential;
    }

    /**
     * @param string $cachedState created from createState() and kept in session
     * @param string $state incoming state string sent from eko
     * @throws InvalidStateException
     */
    public function validateState($cachedState, $state)
    {
        if(empty($state)){
            throw new InvalidStateException('State must not be empty.');
        }

        if($cachedState !== $state){
            throw new InvalidStateException('Invalid state.');
        }
    }


    /**
     * @return null|string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return null|string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return null|string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @return null|string
     */
    public function getTokenUri()
    {
        return $this->tokenUri;
    }

    /**
     * @return null|string
     */
    private function getUserInfoUri()
    {
        return $this->userInfoUri;
    }

    /**
     * @return null|string
     */
    private function getAuthenticateUri()
    {
        return $this->authenticateUri;
    }

    /**
     * @return null|string
     */
    public function getEkoUri()
    {
        return $this->ekoUri;
    }

    /**
     * @return null|string
     */
    private function getScope()
    {
        return $this->scope;
    }

    /**
     * @param null|string $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @param null|string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param null|string $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @param null|string $tokenUri
     */
    public function setTokenUri($tokenUri)
    {
        $this->tokenUri = $tokenUri;
    }

    /**
     * @param null|string $userInfoUri
     */
    public function setUserInfoUri($userInfoUri)
    {
        $this->userInfoUri = $userInfoUri;
    }

    /**
     * @param null|string $authenticateUri
     */
    public function setAuthenticateUri($authenticateUri)
    {
        $this->authenticateUri = $authenticateUri;
    }

    /**
     * @param null|string $ekoUri
     */
    public function setEkoUri($ekoUri)
    {
        $this->ekoUri = $ekoUri;
        $this->setAuthenticateUri($this->ekoUri . '/oauth/authorize');
        $this->setTokenUri($this->ekoUri . '/oauth/token');
        $this->setUserInfoUri($this->ekoUri . '/userinfo');
    }

    /**
     * @param null|string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }


}