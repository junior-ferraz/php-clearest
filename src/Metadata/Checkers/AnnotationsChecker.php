<?php
namespace CleaRest\Metadata\Checkers;

use CleaRest\Capabilities\CapabilitiesFactory;
use CleaRest\Metadata\Annotations\Assert;
use CleaRest\Metadata\Annotations\Body;
use CleaRest\Metadata\Annotations\Enum;
use CleaRest\Metadata\Annotations\Validate;
use CleaRest\Metadata\ConsoleTool;
use CleaRest\Metadata\Descriptors\Clazz;
use CleaRest\Validation\Validator;

/**
 * Checks the consistency of framework annotations such as:
 * @body     : parameter exists in method
 * @assert   : capability exists
 * @validate : parameter exists in method and validation rule is registered
 * @enum     : class or interface exists, parameter exists
 */
class AnnotationsChecker
{
    /**
     * Check validity of arguments set on @body, @assert, @validate and @enum annotations
     * @param Clazz[] $classes
     * @param string[] $capabilitiesIndex
     */
    public static function checkClasses(array $classes, $capabilitiesIndex) {
        foreach ($classes as $class) {
            foreach ($class->annotations as $annotation) {
                if ($annotation instanceof Assert) {
                    if (!array_key_exists($annotation->capability, $capabilitiesIndex)) {
                        ConsoleTool::addError(
                            "Unknown capability {$annotation->capability} " .
                            "defined on @assert in class {$class->name}"
                        );
                    }
                }
            }
            foreach ($class->methods as $method) {
                foreach ($method->annotations as $annotation) {
                    if ($annotation instanceof Assert) {
                        if (!array_key_exists($annotation->capability, $capabilitiesIndex)) {
                            ConsoleTool::addError(
                                "Unknown capability {$annotation->capability} " .
                                "defined on @assert in method {$class->name}:{$method->name}"
                            );
                        }
                    } elseif ($annotation instanceof Body) {
                        if ($annotation->parameter != '*' && !array_key_exists($annotation->parameter, $method->parameters)) {
                            ConsoleTool::addError(
                                "Unknown parameter \${$annotation->parameter} " .
                                "defined on @body in method {$class->name}:{$method->name}"
                            );
                        }
                    } elseif ($annotation instanceof Validate) {
                        if (!array_key_exists($annotation->parameter, $method->parameters)) {
                            ConsoleTool::addError(
                                "Unknown parameter \${$annotation->parameter} " .
                                "defined on @validate in method {$class->name}:{$method->name}"
                            );
                        }
                        if (Validator::getRule($annotation->rule) === null) {
                            ConsoleTool::addWarning(
                                "Unregistered rule {$annotation->rule} " .
                                "defined on @validate in method {$class->name}:{$method->name}"
                            );
                        }
                    } elseif ($annotation instanceof Enum) {
                        if (!class_exists($annotation->class) && !interface_exists($annotation->class)) {
                            ConsoleTool::addError(
                                "Unknown class {$annotation->class} ".
                                "defined for @enum in method {$class->name}:{$method->name}"
                            );
                        }
                        if ($annotation->parameter !== null &&
                            !array_key_exists($annotation->parameter, $method->parameters)) {
                            ConsoleTool::addError(
                                "Unknown parameter \${$annotation->parameter} " .
                                "defined on @enum in method {$class->name}:{$method->name}"
                            );
                        }
                    }
                }
            }
            foreach ($class->properties as $property) {
                foreach ($property->annotations as $annotation) {
                    if ($annotation instanceof Validate) {
                        if (Validator::getRule($annotation->rule) === null) {
                            ConsoleTool::addWarning(
                                "Unregistered rule {$annotation->rule} " .
                                "defined on @validate in property {$class->name}:{$property->name}"
                            );
                        }
                    }
                }
            }
        }
    }
}
