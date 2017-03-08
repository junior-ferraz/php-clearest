<?php
namespace CleaRest\Metadata\Annotations\Registry;

/**
 * Annotation constructor argument
 */
class Arg
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var bool
     */
    public $hasDefaultValue = false;
    /**
     * @var mixed
     */
    public $defaultValue;
}