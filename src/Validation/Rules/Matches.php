<?php
namespace CleaRest\Validation\Rules;

use CleaRest\Validation\ValidationRule;

/**
 * Checks if given string matches regex pattern. Examples of usages:
 * @validate $tags matches /@[-0-9a-zA-Z_]+/
 * @validate $email matches /[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/
 */
class Matches implements ValidationRule
{
    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return bool
     */
    public function validate($value, array $args)
    {
        $pattern = implode(' ', $args);
        return preg_match($pattern, $value);
    }

    /**
     * @param mixed $value
     * @param mixed[] $args
     * @return string
     */
    public function getErrorMessage($value, array $args)
    {
        $pattern = implode(' ', $args);
        return "Given value is does not match pattern $pattern";
    }
}
