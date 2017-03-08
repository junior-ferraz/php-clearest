<?php
namespace CleaRest\Metadata;

use CleaRest\Util\ArrayFunctions;

/**
 * Metadata descriptor base
 */
abstract class Descriptor
{
    const SCOPE_PUBLIC = 1;
    const SCOPE_PROTECTED = 2;
    const SCOPE_PRIVATE = 3;
    /**
     * @var string
     */
    public $name;

    /**
     * @param mixed[string] $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $name => $value) {
            $this->{$name} = $value;
        }
    }

    public function __toString()
    {
        return 'new ' . static::class . '(' . ArrayFunctions::toString((array)$this) . ')';
    }

}
