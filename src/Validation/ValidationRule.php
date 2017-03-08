<?php

namespace CleaRest\Validation;

/**
 * Implement this interface and register your class with Validator::registerRule to make it available for @validate use
 */
interface ValidationRule
{
    /**
     * Validates the $value given the annotation $args
     * @param mixed $value
     * @param mixed[] $args
     * @return bool
     */
    public function validate($value, array $args);

    /**
     * Returns the error message that is shown to the user as a failure reason
     * @param mixed $value
     * @param mixed[] $args
     * @return string
     */
    public function getErrorMessage($value, array $args);
}

