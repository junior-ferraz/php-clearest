<?php
namespace CleaRest\Validation\Rules;

use CleaRest\Validation\ValidationRule;

/**
 * Checks if given value is different tha null. Examples of usage:
 * @validate $price NotNull
 */
class NotNull implements ValidationRule
{

    /**
     * Validation method
     *
     * @param mixed $value
     * @param mixed[] $args
     * @return bool
     */
    public function validate($value, array $args)
    {
        return $value !== null;
    }

    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return string
     */
    public function getErrorMessage($value, array $args)
    {
        return "Given value is null";
    }
}