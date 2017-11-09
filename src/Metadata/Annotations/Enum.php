<?php
namespace CleaRest\Metadata\Annotations;

use CleaRest\Metadata\Annotation;

/**
 * This annotation signs the parameter or property associated
 * to contain a value present in one of the constants of a class or interface.
 *
 * Example on a method:
 *   @enum StatusEnum $status
 *   @param int $status
 *   public function changeStatus($status);
 *
 * Example on a property:
 *   @enum StatusEnum
 *   @var int
 *   public $status;
 *
 * Parameters and properties marked with @enum are automatically validated
 */
class Enum extends Annotation
{
    public $class;

    public $parameter;

    public function __construct($class, $parameter = null)
    {
        $this->class = $class;
        $this->parameter = str_replace('$', '', $parameter);
    }
}

