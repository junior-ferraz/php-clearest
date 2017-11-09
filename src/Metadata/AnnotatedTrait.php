<?php
namespace CleaRest\Metadata;

/**
 * Trait for descriptors that present structures that can be annotated
 */
trait AnnotatedTrait
{
    /**
     * @var string
     */
    public $description;

    /**
     * @var Annotation[]
     */
    public $annotations = [];
}
