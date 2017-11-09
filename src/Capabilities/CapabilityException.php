<?php
namespace CleaRest\Capabilities;


use CleaRest\Api\Exceptions\ApiException;

/**
 * Exception thrown when an @assert annotation fails
 */
class CapabilityException extends ApiException
{
    const UNAUTHORIZED = 1;
    const PAYMENT_REQUIRED = 2;
    const FORBIDDEN = 3;

    protected $codeMap = [
        self::UNAUTHORIZED => 401,
        self::PAYMENT_REQUIRED => 402,
        self::FORBIDDEN => 403,
    ];

    public static function fromCapability(Capability $capability)
    {
        return new self($capability->getErrorMessage(), $capability->getErrorCode());
    }
}
