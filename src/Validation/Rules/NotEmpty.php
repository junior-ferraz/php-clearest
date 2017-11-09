<?php
namespace CleaRest\Validation\Rules;


use CleaRest\Validation\ValidationRule;

/**
 * Checks if given value is not empty (means, not an empty array or null or empty string)
 * @validate $array NotEmpty
 * @validate $name NotEmpty
 */
class NotEmpty implements ValidationRule
{
    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return bool
     */
    public function validate($value, array $args)
    {
        return !empty($value);
    }

    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return string
     */
    public function getErrorMessage($value, array $args)
    {
        return "Given value is empty";
    }
}
