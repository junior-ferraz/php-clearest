<?php

namespace CleaRest\Validation;

use CleaRest\Validation\Context\ValidationContext;

/**
 * This class represent a validation violation with its specific context
 */
class Violation
{
    /**
     * @var string
     */
    public $message;
    /**
     * @var mixed
     */
    public $value;
    /**
     * @var ValidationContext
     */
    public $context;

    public function __construct($message, $value, ValidationContext $context)
    {
        $this->message = $message;
        $this->value = $value;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return ValidationContext
     */
    public function getContext()
    {
        return $this->context;
    }

}