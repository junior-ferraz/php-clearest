<?php

namespace CleaRest\Metadata\Descriptors;

use CleaRest\Metadata\Descriptor;

/**
 * Describes the implementations available for a service
 */
class ServiceInstance extends Descriptor
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $capability;
}
