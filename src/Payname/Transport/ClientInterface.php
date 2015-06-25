<?php
namespace Payname\Transport;

use Payname\ApiConfig;

interface ClientInterface
{
    /**
     * Construct
     *
     * @param ApiConfig $apiConfig ApiConfig for this call. It must be used to pass dynamic configuration and credentials.
     */
    public function __construct(ApiConfig $apiConfig);

    /**
     * @param string $method The HTTP method being used
     * @param string $url The URL being requested
     * @param array $headers Headers to be used in the request
     * @param array $params KV pairs for parameters.
     *
     * @return Response
     */
    public function request($method, $url, $headers, $params);
}
