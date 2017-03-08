<?php
namespace CleaRest\Validation\Rules;


use CleaRest\Validation\ValidationRule;

/**
 * Checks if string length attends to given comparison. Examples of usage:
 * @validate $parameter length > 5
 * @validate $parameter length <= 10
 */
class Length implements ValidationRule
{
    use ComparisionTrait;

    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return bool
     */
    public function validate($value, array $args)
    {
        return $this->compare(strlen($value), $args[0], $args[1]);
    }

    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return string
     */
    public function getErrorMessage($value, array $args)
    {
        return "String length is not $args[0] $args[1]";
    }
}
