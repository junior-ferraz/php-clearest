<?php

namespace CleaRest\Validation\Context;

/**
 * Represents the context of the object that is beling validated
 */
class ObjectContext extends ValidationContext
{
    /**
     * @var string
     */
    public $property;
    /**
     * @var ValidationContext
     */
    public $parent;
}
