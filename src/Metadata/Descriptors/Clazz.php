<?php
namespace CleaRest\Metadata\Descriptors;

use CleaRest\Metadata\AnnotatedTrait;
use CleaRest\Metadata\Descriptor;

/**
 * Contains metadata for classes and interfaces
 */
class Clazz extends Descriptor
{
    use AnnotatedTrait;

    /**
     * @var string
     */
    public $namespace;

    /**
     * @var string
     */
    public $shortName;

    /**
     * @var Method[]
     */
    public $methods = [];

    /**
     * @var Property[]
     */
    public $properties = [];

    /**
     * @var string
     */
    public $extends;

    /**
     * @var string[]
     */
    public $implements = [];

    /**
     * @var string[]
     */
    public $uses;

    /**
     * @var mixed[]
     */
    public $constants;

    /**
     * @var bool
     */
    public $isAbstract = false;

    /**
     * @var bool
     */
    public $isInterface = false;

    /**
     * @var bool
     */
    public $isPlainObject = false;

    /**
     * @var ServiceInstance[]
     */
    public $versions = [];
}
