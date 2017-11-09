<?php
namespace CleaRest\Api\Exceptions;

/**
 * Router related exceptions
 */
class RouterException extends ApiException
{
    const ROUTE_NOT_FOUND = 1;
    const METHOD_NOT_ALLOWED = 2;

    protected $codeMap = [
        self::ROUTE_NOT_FOUND    => 404,
        self::METHOD_NOT_ALLOWED => 405,
    ];
}