<?php
/**
 * Created by PhpStorm.
 * User: juraboonnom
 * Date: 12/15/2017 AD
 * Time: 5:30 PM
 */

namespace EkoApp\OAuth;
use EkoApp\OAuth\Exceptions\InvalidIdTokenException;
use EkoApp\OAuth\Exceptions\InvalidTokenException;
use Firebase\JWT\JWT;

class OAuthToken
{
    /**
     * @var string id token string
     */
    private $rawIdToken;

    /**
     * @var \stdClass id token object
     */
    private $idToken;

    /**
     * @var string access token string
     */
    private $accessToken;

    /**
     * @var string refresh token string
     */
    private $refreshToken;

    /**
     * @var string this will usually be the word “Bearer” (to indicate a bearer token)
     */
    private $tokenType;

    /**
     * @var \DateTime TTL of the access token
     */
    private $expiresIn;

    /**
     * @var string[] data scope of the access token
     */
    private $scope;

    /**
     * OAuthToken constructor.
     * @param string $responseBody
     * @param string $clientSecret
     * @param string $issuer issuer, used to be authorization endpoint
     * @throws InvalidTokenException
     * @throws \Exception
     */
    public function __construct($responseBody, $clientSecret, $issuer)
    {
        $body = json_decode($responseBody);

        if(property_exists($body, 'id_token')){
            $this->setRawIdToken($body->id_token);
            $this->setIdToken($body->id_token, $clientSecret, $issuer);
        }
        else{
            throw new InvalidTokenException('Property "id_token" is undefined.');
        }

        if(property_exists($body, 'access_token')){
            $this->setAccessToken($body->access_token);
        }
        else{
            throw new InvalidTokenException('Property "access_token" is undefined.');
        }

        if(property_exists($body, 'refresh_token')){
            $this->setRefreshToken($body->refresh_token);
        }
        else{
            $this->refreshToken = null;
        }

        if(property_exists($body, 'token_type')){
            $this->setTokenType($body->token_type);
        }
        else{
            throw new InvalidTokenException('Property "token_type" is undefined.');
        }

        if(property_exists($body, 'expires_in')){
            $this->setExpiresIn($body->expires_in);
        }
        else{
            throw new InvalidTokenException('Property "expires_in" is undefined.');
        }

        if(property_exists($body, 'scope')){
            $this->setScope($body->scope);
        }
        else{
            throw new InvalidTokenException('Property "scope" is undefined.');
        }
    }

    /**
     * @return string
     */
    private function getRawIdToken()
    {
        return $this->rawIdToken;
    }

    /**
     * @return \stdClass
     */
    public function getIdToken()
    {
        return $this->idToken;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @return string[]
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $rawIdToken
     */
    private function setRawIdToken($rawIdToken)
    {
        $this->rawIdToken = $rawIdToken;
    }

    /**
     * @param $rawIdToken
     * @param $clientSecret
     * @throws \Exception
     */
    private function setIdToken($rawIdToken, $clientSecret, $issuer)
    {
        try {
            $this->idToken = JWT::decode($rawIdToken, $clientSecret, ['HS256']);
        }
        catch (\Exception $e) {
            throw $e;
        }

        if($issuer !== $this->idToken->iss){
            throw new InvalidIdTokenException('Invalid issuer.');
        }
    }

    /**
     * @param string $accessToken
     */
    private function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param string $refreshToken
     */
    private function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @param string $tokenType
     */
    private function setTokenType($tokenType)
    {
        $this->tokenType = $tokenType;
    }

    /**
     * @param \DateTime $expiresIn
     */
    private function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @param string[] $scope
     */
    private function setScope($scope)
    {
        $this->scope  = preg_split('/\s+/', $scope);
    }


}