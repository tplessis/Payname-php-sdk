<?php
namespace Payname\Auth;

use Payname\Exception\ApiException;
use Payname\Exception\ApiResponseException;
use Payname\Transport\CurlClient;
use Payname\Security\Crypto;
use Payname\ApiConfig;

/**
 * Class OAuthTokenManager
 */
class OAuthTokenManager
{
    protected $apiConfig = null;

    protected $curlClient = null;

    protected $accessToken = null;

    protected $accessValidity = 0;

    protected $refreshToken = null;

    protected $refreshValidity = 0;

    /**
     * Construct
     *
     * @param ApiConfig $apiConfig is the ApiConfig for this call. It must be used to pass dynamic configuration and credentials.
     */
    public function __construct(ApiConfig $apiConfig)
    {
        $this->apiConfig = $apiConfig;
        $this->curlClient = new CurlClient($this->apiConfig);
    }

    /**
     * Save tokens data received from request POST inputs in cache
     *
     * @param string $access_token The access token
     * @param string $refresh_token The refresh token
     * @param int $access_validity The access token validity
     * @param int $refresh_validity The refresh token validity
     *
     * @return void
     */
    public function saveTokensFromInput($access_token, $refresh_token, $access_validity, $refresh_validity)
    {
        $encryptedAccessToken = Crypto::encrypt($access_token, $this->apiConfig->getApiSecret());
        $encryptedRefreshToken = Crypto::encrypt($refresh_token, $this->apiConfig->getApiSecret());
        $this->setTokensInCache(
            $encryptedAccessToken,
            $access_validity,
            $encryptedRefreshToken,
            $refresh_validity
        );
    }

    /**
     * Inject oauth access token into request headers
     *
     * @param array $headers Request headers
     *
     * @throws ApiException If access token is null
     *
     * @return array
     */
    public function injectAccessToken($headers)
    {
        if($this->apiConfig->isSimpleAuthEnabled()) {
            $headers[] = 'Authorization: ' . $this->apiConfig->getApiSecret();
        } else {
            $accessToken = $this->getAccessToken();

            if($accessToken === null) {
                throw new ApiException('Access token is null. You cannot send request without access token.');
            }

            $headers[] = 'Authorization: Bearer ' . $accessToken;
        }

        return $headers;
    }

    /**
     * Request a new access token if needed
     *
     * @return null|string
     */
    public function requestAccessToken()
    {
        return $this->getAccessToken();
    }

    /**
     * Get access token from cache or request a new one if expired
     *
     * @return null|string
     */
    private function getAccessToken()
    {
        // Check for persisted data first
        $token = $this->getAccessTokenFromCache();
        if($token) {
            $this->accessValidity = $token['accessValidity'];
            $this->accessToken = Crypto::decrypt($token['accessTokenEncrypted'], $this->apiConfig->getApiSecret());
            $this->refreshValidity = $token['refreshValidity'];
            $this->refreshToken = Crypto::decrypt($token['refreshTokenEncrypted'], $this->apiConfig->getApiSecret());
        }

        // If access token has expired, refresh it
        if($this->accessToken != null && $this->accessValidity < time()) {
            $this->refreshAccessToken();
        }

        // If accessToken is null, request a new access token
        if ($this->accessToken == null) {
            $this->createAccessToken();
        }

        return $this->accessToken;
    }

    /**
     * Create an access token by making an API call
     *
     * @throws ApiResponseException If an error HTTP code is returned from API call
     *
     * @return void
     */
    private function createAccessToken()
    {
        $url = $this->apiConfig->getApiBaseUrl() . '/auth/token';
        $headers = [];
        $headers['Content-Type'] = 'application/json';
        $payLoad = array('ID' => $this->apiConfig->getApiId(), 'secret' => $this->apiConfig->getApiSecret());

        $response = $this->curlClient->request('POST', $url, $headers, $payLoad);

        // API call return an HTTP error code ? Throw an exception
        if ($response->getHttpCode() < 200 || $response->getHttpCode() >= 300) {
            throw new ApiResponseException($response->getMessage(), $response);
        }
    }

    /**
     * Refresh access token by sending the given refresh token
     *
     * @throws ApiException If response is not well formed
     * @throws ApiResponseException If an error HTTP code is returned from API call
     *
     * @return void
     */
    private function refreshAccessToken()
    {
        // If refresh token has expired or is null, set access token to null to generate a new one
        if($this->refreshToken != null && $this->refreshValidity < time()) {
            $this->accessToken = null;
        } else {
            // Send an API call to refresh access token
            $url = $this->apiConfig->getApiBaseUrl() . '/auth/refresh_token';
            $headers = [];
            $headers['Content-Type'] = 'application/json';
            $payLoad = array('ID' => $this->apiConfig->getApiId(), 'token' => $this->refreshToken);
            $response = $this->curlClient->request('POST', $url, $headers, $payLoad);

            // API call return an HTTP error code ? Throw an exception
            if ($response->getHttpCode() < 200 || $response->getHttpCode() >= 300) {
                throw new ApiResponseException($response->getMessage(), $response);
            }

            // Extract new access token and its validity from API Response
            if($response->isSuccess()) {
                $responseBody = $response->getBody();

                if(isset($responseBody['data']) && is_array($responseBody['data'])) {
                    $this->accessToken = $responseBody['data']['access_token'];
                    $this->accessValidity = $responseBody['data']['access_validity'];

                    // Store access token data in cache
                    $this->setTokensInCache(
                        Crypto::encrypt($this->accessToken, $this->apiConfig->getApiSecret()),
                        $this->accessValidity,
                        Crypto::encrypt($this->refreshToken, $this->apiConfig->getApiSecret()),
                        $this->refreshValidity
                    );
                } else {
                    throw new ApiException('Datas are missing from response.');
                }
            } else {
                throw new ApiException('Unable to refresh access token : ' . $response->getMessage());
            }
        }
    }

    /**
     * Get access token from cache
     *
     * @return null|string
     */
    private function getAccessTokenFromCache()
    {
        $tokens = null;
        $cachePath = $this->apiConfig->getTokenCachePath() . '/token.cache';

        if (file_exists($cachePath)) {
            $cachedToken = file_get_contents($cachePath);
            if ($cachedToken) {
                $tokens = json_decode($cachedToken, true);
                if ($this->apiConfig->getApiId() && is_array($tokens) && array_key_exists($this->apiConfig->getApiId(), $tokens)) {
                    return $tokens[$this->apiConfig->getApiId()];
                }
            }
        }

        return $tokens;
    }

    /**
     * Set given tokens information in cache file
     *
     * @param string $accessToken Encrypted access token
     * @param int $accessValidity Access token validity
     * @param string $refreshToken Encrypted refresh token
     * @param int $refreshValidity Refresh token validity
     *
     * @throws ApiException If cannot create var directory or write content in cache file
     *
     * @return void
     */
    private function setTokensInCache($accessToken, $accessValidity, $refreshToken, $refreshValidity)
    {
        $tokensData = [];
        $cachePath = $this->apiConfig->getTokenCachePath() . '/token.cache';

        if (!is_dir(dirname($cachePath))) {
            if (mkdir(dirname($cachePath), 0755, true) == false) {
                throw new ApiException('Failed to create directory at ' . $cachePath);
            }
        }

        $tokensData[$this->apiConfig->getApiId()] = array(
            'clientId' => $this->apiConfig->getApiId(),
            'accessTokenEncrypted' => $accessToken,
            'accessValidity' => $accessValidity,
            'refreshTokenEncrypted' => $refreshToken,
            'refreshValidity' => $refreshValidity
        );

        if(!file_put_contents($cachePath, json_encode($tokensData))) {
            throw new ApiException('Failed to write cache');
        };
    }
}
