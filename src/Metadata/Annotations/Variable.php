<?php
namespace CleaRest\Metadata\Annotations;

use CleaRest\Metadata\Annotation;

/**
 * Assigns a type to a property. Example:
 *
 * @var int
 */
class Variable extends Annotation
{
    public $type;

    public function __construct($type)
    {
        $this->type = $type;
    }
}
