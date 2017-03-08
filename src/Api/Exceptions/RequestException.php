<?php
namespace CleaRest\Api\Exceptions;


/**
 * Request related exceptions
 */
class RequestException extends ApiException
{
    const INVALID_CONTENT_TYPE = 1;
    const MISSING_HEADER = 2;
    const INVALID_BODY = 3;
    const MISSING_FIELD = 4;
    const INVALID_FIELD = 5;

    protected $codeMap = [
        self::INVALID_CONTENT_TYPE => 406,
        self::MISSING_HEADER => 412,
        self::INVALID_BODY => 400,
        self::MISSING_FIELD => 400,
        self::INVALID_FIELD => 400,
    ];
}