<?php

namespace CleaRest\Validation;

use CleaRest\FrameworkException;
use CleaRest\Validation\Context\MethodContext;
use CleaRest\Validation\Context\ValidationContext;

/**
 * This exception is thrown when the a parameter or property validation fails
 */
class ValidationException extends FrameworkException
{
    /**
     * @var Violation[]
     */
    private $violations;

    /**
     * @var bool
     */
    private $apiVisible = true;

    /**
     * @param Violation[] $violations
     * @param ValidationContext|null $context
     */
    public function __construct(array $violations, ValidationContext $context = null)
    {
        $message = "Validation failed";
        if ($context instanceof MethodContext) {
            $message .= ' calling method ' . $context->class . ":" . $context->method;
        }
        $message .= ':\n';
        foreach ($violations as $k => $violation) {
            $message .= $k . ". " . $violation->getMessage() . " on field " . $violation->getContext()->getFieldName();
        }
        parent::__construct($message);
        $this->violations = $violations;
    }

    /**
     * @return Violation[]
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @param bool $apiVisible
     */
    public function setApiVisible($apiVisible)
    {
        $this->apiVisible = $apiVisible;
    }

    /**
     * @return bool
     */
    public function isApiVisible()
    {
        return $this->apiVisible;
    }
}