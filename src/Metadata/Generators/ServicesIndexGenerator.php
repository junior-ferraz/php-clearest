<?php
namespace CleaRest\Metadata\Generators;

use CleaRest\Metadata\Annotations\Assert;
use CleaRest\Metadata\Annotations\Version;
use CleaRest\Metadata\ConsoleTool;
use CleaRest\Metadata\Descriptors\Clazz;
use CleaRest\Metadata\Descriptors\ServiceInstance;
use CleaRest\Services\Service;

/**
 * Generates the implementation index for services interface
 */
class ServicesIndexGenerator
{
    /**
     * @param Clazz[] $classesMetadata
     */
    public static function generate(array $classesMetadata)
    {
        /** @var Clazz[] $interfaces */
        $interfaces = [];
        /** @var Clazz[] $implementations */
        $implementations = [];
        foreach ($classesMetadata as $class) {
            if (in_array(Service::class, $class->implements)) {
                if ($class->isInterface) {
                    $interfaces[$class->name] = $class;
                } elseif (!$class->isAbstract) {
                    $implementations[$class->name] = $class;
                }
                foreach ($class->methods as $method) {
                    foreach ($method->parameters as $parameter) {
                        if (is_array($parameter->type)) {
                            ConsoleTool::addError(
                                "Invalid type for parameter \${$parameter->name} " .
                                "in method {$class->name}:{$method->name}. " .
                                "Only one type allowed."
                            );
                        }
                    }
                }
            }
        }

        foreach ($interfaces as $interface) {
            foreach ($implementations as $class) {
                if (!in_array($interface->name, $class->implements)) {
                    continue;
                }

                $version = null;
                $assert = null;
                foreach ($class->annotations as $annotation) {
                    if ($annotation instanceof Version) {
                        $version = $annotation;
                    } elseif ($annotation instanceof Assert) {
                        $assert = $annotation;
                    }
                }
                if ($version === null) {
                    ConsoleTool::addError("No @version annotation found in {$class->name}");
                    continue;
                }
                if (isset($interface->versions[$version->number])) {
                    ConsoleTool::addError(
                        "Version {$version->number} defined in {$class->name} is already in use " .
                        "for class " . $interface->versions[$version->number]
                    );
                    continue;
                }
                $instance = new ServiceInstance();
                $instance->class = $class->name;

                if ($assert !== null) {
                    $instance->capability = $assert->capability;
                }

                if (strlen($version->name) > 0) {
                    $instance->name = $version->name;
                    foreach ($interfaces as $i) {
                        foreach ($i->versions as $v) {
                            if ($v->name == $version->name) {
                                ConsoleTool::addError(
                                    "Version name \"{$version->name}\" defined in {$class->name} is already in use " .
                                    "for class {$v->class}"
                                );
                                $instance = null;
                                break 2;
                            }
                        }
                    }

                }
                if ($instance !== null) {
                    $interface->versions[$version->number] = $instance;
                }
            }
            ksort($interface->versions);
        }
    }
}
