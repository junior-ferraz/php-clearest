<?php

namespace CleaRest\Metadata\Annotations;

use CleaRest\Metadata\Annotation;

/**
 * This annotation assigns a parameter or property to be validated against specified rule.
 *
 * Example in methods:
 * @validate $age >= 18
 * @param int $age
 * public function accessPorn($age)
 *
 * In properties, the first parameter can be ignored, since it's the parameter name which is the property it's on.
 * Examples:
 *
 * @validate length > 8
 * @var string
 * public $password
 *
 * @validate NotNull
 * @var User
 * public $user;
 */
class Validate extends Annotation
{
    public $parameter;

    public $rule;

    public $args = [];

    public function __construct()
    {
        $funcArgs = func_get_args();
        $firstParam = array_shift($funcArgs);

        if (substr($firstParam, 0, 1) == '$') {
            $this->parameter = substr($firstParam, 1);
            $this->rule = array_shift($funcArgs);
        } else {
            $this->rule = $firstParam;
        }

        if (count($funcArgs) > 0) {
            $this->args = $funcArgs;
        }
    }
}
