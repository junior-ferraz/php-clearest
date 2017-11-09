<?php
namespace CleaRest\Validation\Rules;

use CleaRest\Validation\ValidationRule;

/**
 * Checks if value is smaller than given value. Example of usage:
 * @validate $parameter < 10
 */
class SmallerThan implements ValidationRule
{
    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return bool
     */
    public function validate($value, array $args)
    {
        return $value < $args[0];
    }

    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return string
     */
    public function getErrorMessage($value, array $args)
    {
        return "Given value is not smaller than $args[0]";
    }
}
