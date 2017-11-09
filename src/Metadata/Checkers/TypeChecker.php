<?php

namespace CleaRest\Metadata\Checkers;

use CleaRest\Metadata\Annotations\Enum;
use CleaRest\Metadata\Annotations\Param;
use CleaRest\Metadata\Annotations\ReturnType;
use CleaRest\Metadata\Annotations\Throws;
use CleaRest\Metadata\Annotations\Variable;
use CleaRest\Metadata\ConsoleTool;
use CleaRest\Metadata\Descriptor;
use CleaRest\Metadata\Descriptors\Clazz;
use CleaRest\Metadata\Descriptors\Type;

/**
 * Checks the validity of type defined in annotations @var, @param, @return and @throws.
 */
class TypeChecker
{
    /**
     * @param Clazz[] $classes
     */
    public static function checkClasses($classes) {
        // This is a dirty hack, children need to know classes annotated in parent
        // TODO: think in a better solution
        foreach ($classes as $parent) {
            foreach ($classes as $class) {
                if ($parent->name === $class->name) {
                    continue;
                }
                if (is_subclass_of($class->name, $parent->name)) {
                    foreach ($parent->uses as $alias => $usedClass) {
                        if (!array_key_exists($alias, $class->uses)) {
                            $class->uses[$alias] = $usedClass;
                        }
                    }
                }
            }
        }
        foreach ($classes as $interface) {
            if ($interface->isInterface) {
                self::checkMethods($interface);
                foreach ($classes as $class) {
                    if (!in_array($interface->name, $class->implements)) {
                        continue;
                    }
                    // inherits interfaces annotations
                    $class->uses = array_merge($interface->uses, $class->uses);
                    foreach ($class->methods as $method) {
                        if (array_key_exists($method->name, $interface->methods)
                            && empty($method->annotations)
                            && $method->description === null
                        ) {
                            $interfaceMethod = $interface->methods[$method->name];
                            $method->annotations = $interfaceMethod->annotations;
                            $method->description = $interfaceMethod->description;
                        }
                    }
                }
            }
        }
        foreach ($classes as $class) {
            if (!$class->isInterface) {
                self::checkMethods($class);
                self::checkProperties($class);
            }
        }
    }

    private static function checkMethods(Clazz $class)
    {
        foreach ($class->methods as $method) {
            // find @return type
            foreach ($method->annotations as $annotation) {
                if ($annotation instanceof ReturnType) {
                    $method->return = self::extractTypes($annotation->type, $class);
                } elseif ($annotation instanceof Throws) {
                    $annotation->class = self::extractTypes($annotation->class, $class)->name;
                } elseif ($annotation instanceof Enum) {
                    $annotation->class = self::extractTypes($annotation->class, $class)->name;
                }
            }
            foreach ($method->parameters as $parameter) {
                // find type on method's @param annotation
                foreach ($method->annotations as $annotation) {
                    if ($annotation instanceof Param && $annotation->name == $parameter->name) {
                        $parameter->type = self::extractTypes($annotation->type, $class);
                        $parameter->description = $annotation->description;
                        break;
                    } elseif ($annotation instanceof Enum && $annotation->parameter == $parameter->name) {
                        $parameter->enum = $annotation->class;
                        if (!class_exists($parameter->enum) && !interface_exists($parameter->enum)) {
                            ConsoleTool::addError(
                                "Class or interface {$annotation->class} used in @enum on " .
                                "method {$class->name}:{$method->name} " .
                                "not found"
                            );
                        }
                    }
                }
                if (empty($parameter->type) && $method->scope != Descriptor::SCOPE_PRIVATE) {
                    $paramFullName = "\${$parameter->name} of {$class->name}:{$method->name}";
                    ConsoleTool::addError("Parameter $paramFullName does not have type defined in @param annotation");
                }
            }
        }
    }

    private static function checkProperties(Clazz $class)
    {
        foreach ($class->properties as $property) {
            // find type on property's @var annotation
            foreach ($property->annotations as $annotation) {
                if ($annotation instanceof Variable) {
                    $property->type = self::extractTypes($annotation->type, $class);
                    break;
                } elseif ($annotation instanceof Enum) {
                    $property->enum = self::extractTypes($annotation->class, $class)->name;
                    if (!class_exists($property->enum) && !interface_exists($property->enum)) {
                        ConsoleTool::addError(
                            "Class or interface {$annotation->class} used in @enum on " .
                            "property {$class->name}:{$property->name} " .
                            "not found"
                        );
                    }
                    $annotation->class = $property->enum;
                }
            }
            if (empty($property->type) && $property->scope != Descriptor::SCOPE_PRIVATE) {
                $propertyFullName = "\${$property->name} of {$class->name}";
                ConsoleTool::addError("Property $propertyFullName does not have type defined in @var annotation");
            }
        }
    }

    /**
     * @param string $typeString
     * @param Clazz $declaredClass
     * @return Type|Type[]
     */
    private static function extractTypes($typeString, Clazz $declaredClass)
    {
        /** @var Type[] $types */
        $types = [];
        $typesString = explode('|', $typeString);
        foreach ($typesString as $string) {
            $types[] = $type = new Type();

            if ($string === 'array') {
                $type->name = 'mixed';
                $type->isArray = true;
                continue;
            }

            if (preg_match('/(.+)\[.*\]/', $string, $matches)) {
                $string = $matches[1];
                $type->isArray = true;
            }

            // Are we talking about the current class?
            if ($string == $declaredClass->shortName) {
                $type->name = $declaredClass->name;
                continue;
            }

            // is the type namespaced? then just use it without slash
            if (substr($string, 0, 1) == '\\') {
                $type->name = substr($string, 1);
                continue;
            }

            // maybe it's declared with an alias in an "use" header
            if (array_key_exists($string, $declaredClass->uses)) {
                $type->name = $declaredClass->uses[$string];
                continue;
            }

            // or it's even relative to the current class
            $fullName = $declaredClass->namespace . '\\' . $string;
            if (class_exists($fullName) || interface_exists($fullName)) {
                $type->name = $fullName;
                continue;
            }

            // fall back to original type
            $type->name = $string;

        }

        switch (count($types)) {
            case 0:  return null;
            case 1:  return array_pop($types);
            default: return $types;
        }
    }
}

