<?php
namespace Payname\Api;

use Payname\ApiConfig;
use Payname\Transport\CurlClient;
use Payname\Auth\OAuthTokenManager;
use Payname\Exception\ApiException;
use Payname\Exception\ApiResponseException;

abstract class ApiCall
{
    protected $model = null;

    protected $apiConfig = null;

    protected $curlClient = null;

    protected $oauthManager = null;

    /**
     * Construct
     *
     * @param ApiConfig $apiConfig ApiConfig for this call. It must be used to pass dynamic configuration and credentials.
     *
     * @throws ApiException If ApiConfig is not set
     */
    public function __construct(ApiConfig $apiConfig)
    {
        if(null === $apiConfig) {
            throw new ApiException('ApiConfig must be set.');
        }

        $this->apiConfig = $apiConfig;

        $this->reflection = new \ReflectionClass($this);
        $classShortName = 'Payname\\Models\\' . $this->reflection->getShortName();

        if(class_exists($classShortName)) {
            $this->model = new $classShortName;
        }
    }

    /**
     * Execute SDK call to Payname API
     *
     * @param string      $url
     * @param string      $method
     * @param string      $payLoad
     * @param array       $headers
     *
     * @throws ApiResponseException If an error HTTP code is returned from API call
     *
     * @return string json response of the object
     */
    protected function executeCall($url, $method, $payLoad, $headers = array())
    {
        // Add config base url
        $url = $this->apiConfig->getApiBaseUrl() . $url;

        // Inject the authorization token in the headers
        $oauthManager = new OAuthTokenManager($this->apiConfig);
        $headers = $oauthManager->injectAccessToken($headers);

        // We send JSON data
        $headers['Content-Type'] = 'application/json';

        // Make execution call
        $this->curlClient = new CurlClient($this->apiConfig);
        $response = $this->curlClient->request($method, $url, $headers, $payLoad);

        // API call return an HTTP error code ? Throw an exception
        if ($response->getHttpCode() < 200 || $response->getHttpCode() >= 300) {
            throw new ApiResponseException($response->getMessage(), $response);
        }

        return $response;
    }

    /**
     * Magic call method
     *
     * @param string $method Method name to call
     * @param array $args Method arguments
     *
     * @throws ApiException If model or method does not exists
     *
     * @return mixed
     */
    public function __call($method, $args = [])
    {
        if(null !== $this->model) {
            if(is_callable(array($this->model, $method), true)) {
                return call_user_func_array(array($this->model, $method), $args);
            } else {
                throw new ApiException('Method name does not exist for this API call.');
            }
        } else {
            throw new ApiException('API call model was not initialized.');
        }
    }

}
