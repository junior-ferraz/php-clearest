<?php
namespace CleaRest\Metadata\Descriptors;

use CleaRest\Metadata\AnnotatedTrait;
use CleaRest\Metadata\Descriptor;

/**
 * Contains metadata for methods
 */
class Method extends Descriptor
{
    use AnnotatedTrait;

    /**
     * @var int
     */
    public $scope;
    /**
     * @var Parameter[]
     */
    public $parameters = [];
    /**
     * @var Type|Type[]
     */
    public $return;
}
