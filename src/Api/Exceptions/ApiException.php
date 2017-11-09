<?php

namespace CleaRest\Api\Exceptions;

use CleaRest\Api\Network\HttpException;
use Exception;

/**
 * Extend this class to create your own exception type that is automatically handled by the ApiExceptionHandler.
 * The child class should implement simply the $codeMap with mapping between exception codes and HTTP status codes.
 */
abstract class ApiException extends HttpException
{
    /**
     * This attribute should contain the map from exception code (as key) and http code (as value)
     * @var int[]
     */
    protected $codeMap = [];

    /**
     * @param string $message
     * @param int $code
     * @param array|null $data
     * @param Exception|null $previous
     */
    public function __construct($message = "", $code, array $data = null, Exception $previous = null)
    {
        parent::__construct($message, 500, $data, $code, $previous);
    }

    public function getHttpCode()
    {
        if (isset($this->codeMap[$this->getCode()])) {
            return $this->codeMap[$this->getCode()];
        }
        return parent::getHttpCode();
    }

}