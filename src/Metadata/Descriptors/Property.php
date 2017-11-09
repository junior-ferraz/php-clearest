<?php

namespace CleaRest\Metadata\Descriptors;

use CleaRest\Metadata\AnnotatedTrait;

/**
 * Contains metadata for classes' properties
 */
class Property extends Value
{
    use AnnotatedTrait;

    /**
     * @var int
     */
    public $scope;
}
