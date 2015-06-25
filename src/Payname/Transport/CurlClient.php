<?php
namespace Payname\Transport;

use Payname\Exception\ApiException;
use Payname\ApiConfig;

class CurlClient implements ClientInterface
{
    // @var array HTTP methods that could be used over the API
    private static $availableMethods = array('get', 'put', 'post', 'delete');

    protected $apiConfig = null;

    /**
     * Construct
     *
     * @param Payname\ApiConfig $apiConfig is the ApiConfig for this call. It must be used to pass dynamic configuration and credentials.
     */
    public function __construct(ApiConfig $apiConfig)
    {
        $this->apiConfig = $apiConfig;
    }

    /**
     * @param string $method The HTTP method being used
     * @param string $url The URL being requested
     * @param array $headers Headers to be used in the request
     * @param array $params KV pairs for parameters.
     *
     * @throws ApiException If method is not an available method
     *
     * @return Response
     */
    public function request($method, $url, $headers, $params = array())
    {
        $curl = curl_init();
        $method = strtolower($method);
        $opts = array();

        if(!in_array($method, self::$availableMethods)) {
            throw new ApiException('Unrecognized method ' . $method);
        }

        if(is_array($params)) {
            $params = http_build_query($params);
        }

        switch($method) {
            case 'get':
                $opts[CURLOPT_HTTPGET] = 1;
                break;
            case 'put':
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            case 'post':
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            case 'delete':
                break;
        }

        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = 30;
        $opts[CURLOPT_TIMEOUT] = 80;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_HTTPHEADER] = $headers;

        curl_setopt_array($curl, $opts);

        $responseBody = curl_exec($curl);
        $responseErrorMessage = curl_error($curl);
        $responseErrorNumber = curl_errno($curl);
        $responseHttpCode= curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($responseBody === false) {
            $this->handleError($url, $responseErrorNumber, $responseErrorMessage, $responseHttpCode);
        }

        return new Response($responseBody, $responseHttpCode);
    }

    /**
     * Handle request error
     *
     * @param string $url Url called
     * @param number $responseError The error number
     * @param string $responseMessage The error message
     *
     * @throws ApiException A new ApiException is throws when an error is found
     *
     * @return void
     */
    private function handleError($url, $responseError, $responseMessage, $responseCode)
    {
        switch ($responseError) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $msg = 'Could not connect to Payname API (' . $url . ')';
                break;
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PEER_CERTIFICATE:
                $msg = 'Could not verify Payname\'s SSL certificate';
                break;
            default:
                $msg = 'Unexpected error when communicating with Payname API (' . $url . ')';
        }

        $msg .= ' : ' . $responseMessage . '.';

        throw new ApiException($msg, $responseCode);
    }

}
