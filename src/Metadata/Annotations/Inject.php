<?php
namespace CleaRest\Metadata\Annotations;

use CleaRest\Metadata\Annotation;

/**
 * This annotation is used on services protected properties to sign its value is a dependency.
 * The dependency injector will automatically set its value to an implementation of the interface assigned.
 *
 * Example:
 * @inject
 * @var MyServiceInterface
 * protected $myDependency;
 *
 * It's also possible to specify the exact version you want to inject. Example:
 *
 * @inject 3
 */
class Inject extends Annotation
{
    public $version;

    public function __construct($version = null)
    {
        $this->version = $version;
    }
}
