<?php
namespace CleaRest\Metadata\Descriptors;

use CleaRest\Metadata\Descriptor;

/**
 * Abstract descriptor used by Parameter and Property descriptors
 */
abstract class Value extends Descriptor
{
    /**
     * @var Type|Type[]
     */
    public $type;

    /**
     * @var mixed
     */
    public $default;

    /**
     * @var bool
     */
    public $hasDefault = false;

    /**
     * @var string
     */
    public $enum;
}
