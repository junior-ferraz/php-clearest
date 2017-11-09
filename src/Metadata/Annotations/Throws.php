<?php
namespace CleaRest\Metadata\Annotations;

use CleaRest\Api\Network\HttpException;
use CleaRest\Capabilities\CapabilityException;
use CleaRest\Metadata\Annotation;

/**
 * Hints what exceptions can be thrown by the method.
 * This annotation also shows up in the auto generated documentation as an error response if the exception is handled.
 * Examples:
 *
 * @throws MyException
 * @throws CapabilityException FORBIDDEN User does not have access to feature
 * @throws HttpException 404 Entity could not be found
 */
class Throws extends Annotation
{
    public $class;

    public $code;

    public $description;

    public function __construct($class, $code = 0, $description = null)
    {
        $this->class = $class;
        $this->code = $code;

        // The description can be splited in many arguments
        $args = func_get_args();
        $this->description = join(' ', array_slice($args, 2));
    }
}

