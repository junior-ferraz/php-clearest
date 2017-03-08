<?php
namespace CleaRest\Metadata\Annotations;

use CleaRest\Metadata\Annotation;

/**
 * This annotation is used in two cases:
 *
 *  - on method: asserts capability
 *      if fails throws an exception
 *      if succeeds allow method call
 *      Example: @assert CanCreateEntity
 *
 *  - on service class implementation: is used to check if the implementation can be loaded by the factory
 *      if fails, go to a lower version
 *      if succeeds create current instance.
 *      Example: @assert IsBetaUser
 */
class Assert extends Annotation
{
    public $capability;

    public function __construct($capability)
    {
        $this->capability = $capability;
    }
}
