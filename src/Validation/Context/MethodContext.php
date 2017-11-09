<?php

namespace CleaRest\Validation\Context;

/**
 * Represents the context of the method that is being validated before call
 */
class MethodContext extends ValidationContext
{
    /**
     * @var string
     */
    public $method;
    /**
     * @var string
     */
    public $parameter;
}
