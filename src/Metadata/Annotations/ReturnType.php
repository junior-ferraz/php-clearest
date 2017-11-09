<?php
namespace CleaRest\Metadata\Annotations;

use CleaRest\Metadata\Annotation;

/**
 * Defines the return type of a method. Example:
 *
 * @return \DateTime
 * public function getBirthday()
 */
class ReturnType extends Annotation
{
    public $type;

    public function __construct($type)
    {
        $this->type = $type;
    }
}
