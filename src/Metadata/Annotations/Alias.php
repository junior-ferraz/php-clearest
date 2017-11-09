<?php

namespace CleaRest\Metadata\Annotations;

use CleaRest\Metadata\Annotation;

/**
 * This annotation is used to assign an alias to a Capability implementation.
 * This annotation has only effect on the class' doc comment.
   Example of usage:
 *
 * @alias CanRemoveEntity
 */
class Alias extends Annotation
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
