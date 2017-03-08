<?php
namespace CleaRest\Metadata\Annotations\Registry;

/**
 * Annotation entry
 */
class Entry
{
    /**
     * @var string
     */
    public $class;
    /**
     * @var \ReflectionClass
     */
    public $reflection;
    /**
     * @var Arg[]
     */
    public $args = [];
}