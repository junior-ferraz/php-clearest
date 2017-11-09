<?php
namespace CleaRest\Metadata\Annotations;


use CleaRest\Metadata\Annotation;

/**
 * This annotation is used to assign the body content to a specific parameter method. Example:
 *
 * @body $parameter
 *
 * If parameter name is set to *, all parameters will be expected to come from the body, each one as a property.
 *
 * @body *
 */
class Body extends Annotation
{
    public $parameter;

    public function __construct($parameter)
    {
        $this->parameter = str_replace('$', '', $parameter);
    }
}
