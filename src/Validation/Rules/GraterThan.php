<?php
namespace CleaRest\Validation\Rules;


use CleaRest\Validation\ValidationRule;

/**
 * Checks if value is grater than given value. Example of usage:
 * @validate $parameter > 10
 */
class GraterThan implements ValidationRule
{

    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return bool
     */
    public function validate($value, array $args)
    {
        return $value > $args[0];
    }

    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return string
     */
    public function getErrorMessage($value, array $args)
    {
        return "Given value is not grater than $args[0]";
    }
}