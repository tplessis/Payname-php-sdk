<?php
namespace Payname\Exception;

use Payname\Transport\Response;

class ApiResponseException extends \Exception
{
    protected $response;

    /**
     * Magic call method
     *
     * @param string $message [optional] The Exception message to throw.
     * @param \Payname\Transport\Response $response [optional] The Response associated to Exception
     *
     * @return mixed
     */
    public function __construct($message = "", Response $response)
    {
        $this->message = $message;
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
