<?php

namespace CleaRest\Api\Network;


use Exception;

/**
 * When this exception is thrown, it's HttpCode, message and data is returned to the client
 */
class HttpException extends \Exception
{
    /**
     * @var int
     */
    private $httpCode;
    /**
     * @var mixed
     */
    private $data;

    public function __construct($message = "", $httpCode = 500, array $data = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->httpCode = $httpCode;
        $this->data = $data;
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function getData()
    {
        return $this->data;
    }
}
