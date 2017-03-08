<?php
namespace CleaRest\Metadata\Annotations;

use CleaRest\Metadata\Annotation;

/**
 * Defines a parameter type. Example
 *
 * @param string $name parameter's description
 */
class Param extends Annotation
{
    public $type;

    public $name;

    public $description;

    public function __construct($type, $name, $description = null)
    {
        $this->type = $type;
        $this->name = str_replace('$', '', $name);

        // The description can be splited in many arguments
        $args = func_get_args();
        $this->description = join(' ', array_slice($args, 2));
    }
}
