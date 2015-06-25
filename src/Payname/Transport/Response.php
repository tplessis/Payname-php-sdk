<?php
namespace Payname\Transport;

class Response
{
    private $http_code;

    private $error_code;

    private $rawBody;

    private $body;

    private $success;

    private $log;

    private $msg;

    /**
     * Construct
     *
     * @param string $body Response body message
     * @param int $http_code Response HTTP code
     */
    public function __construct($body, $http_code)
    {
        $this->http_code = $http_code;
        $this->rawBody = $body;

        if($this->hasBody()) {
            $this->parseBody();
        }
    }

    private function parseBody()
    {
        $this->body = json_decode($this->rawBody, 1);

        if(array_key_exists('success', $this->body)) {
            $this->success = $this->body['success'];
        }

        if(array_key_exists('msg', $this->body)) {
            $this->msg = $this->body['msg'];
        }

        if(array_key_exists('error', $this->body)) {
            $this->error_code = $this->body['error'];
        }

        if(array_key_exists('log', $this->body)) {
            $this->log = $this->body['log'];
        }
    }

    public function isSuccess()
    {
        return $this->success;
    }

    public function hasError()
    {
        return $this->http_code >= 400;
    }

    public function hasBody()
    {
        return !empty($this->rawBody);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHttpCode()
    {
        return $this->http_code;
    }

    public function getErrorCode()
    {
        return $this->error_code;
    }

    public function getMessage()
    {
        return $this->msg;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function __toString()
    {
        return $this->rawBody;
    }

}
