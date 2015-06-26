<?php

namespace Payname;

class ApiConfig
{
    // @var string The base URL for the Payname API.
    private $apiBaseUrl = 'https://api.payname.fr/v2';

    // @var string The version of the Payname API
    private $apiVersion = '2.0.0';

    // @var string The path where
    private $tokenCachePath = '';

    // @var string Your API ID from your Payname account.
    private $apiId = '';

    // @var string Your API Secret from your Payname account.
    private $apiSecret = '';

    // @var bool If simple auth is enabled. Must be activated in you Payname account.
    private $simpleAuthEnabled = false;

    /**
     *
     * @param string $apiId AP Id from your Payname account
     * @param string $apiSecret AP secret from your Payname account
     *
     * @return ApiConfig
     */
    public function __construct($apiId, $apiSecret)
    {
        $this->apiId = $apiId;
        $this->apiSecret = $apiSecret;

        // Default cache path
        $this->tokenCachePath = __DIR__ . '/../../../var';
    }

    /**
     * @return string The API secret used for requests.
     */
    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * Sets the API secret to be used for requests.
     *
     * @param string $apiSecret
     */
    public function setApiSecret($apiSecret)
    {
        $this->apiSecret = $apiSecret;
    }

    /**
     * @return string The current API ID.
     */
    public function getApiId()
    {
        return $this->apiId;
    }

    /**
     * @param string $apiId The current API ID.
     */
    public function setApiId($apiId)
    {
        $this->apiId = $apiId;
    }

    /**
     * @return string The current API version.
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @return string The base API url
     */
    public function getApiBaseUrl()
    {
        return $this->apiBaseUrl;
    }

    /**
     * @return string The token cache path
     */
    public function getTokenCachePath()
    {
        return $this->tokenCachePath;
    }

    /**
     * @param string $path The new token cache path
     */
    public function setTokenCachePath($path)
    {
        $this->tokenCachePath = realpath($path);
    }

    /**
     * @param bool $simpleAuth Set to true to enabled simple authentification
     */
    public function setSimpleAuthEnabled($simpleAuth)
    {
        $this->simpleAuthEnabled = $simpleAuth;
    }

    /**
     * @return bool Is simple authentification enabled ?
     */
    public function isSimpleAuthEnabled()
    {
        return $this->simpleAuthEnabled;
    }
}
