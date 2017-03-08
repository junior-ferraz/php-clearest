<?php
namespace CleaRest\Metadata\Annotations;

use CleaRest\Metadata\Annotation;

/**
 * This annotation assigns a version number and name to a service implementation.
 * This annotation is mandatory in concrete classes that implement a service interface.
 * Examples of usage:
 *
 * @version 1
 * @version 2 Beta
 */
class Version extends Annotation
{
    public $number;

    public $name;

    public function __construct($number, $name = null)
    {
        $this->number = $number;
        $this->name = $name;
    }
}
