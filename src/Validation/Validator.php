<?php
namespace CleaRest\Validation;

use CleaRest\Api\Data\UploadedFile;
use CleaRest\FrameworkException;
use CleaRest\Metadata\Annotations\Validate;
use CleaRest\Metadata\Descriptors\Method;
use CleaRest\Metadata\Descriptors\Property;
use CleaRest\Metadata\Descriptors\Type;
use CleaRest\Metadata\Descriptors\Value;
use CleaRest\Metadata\MetadataStorage;
use CleaRest\Validation\Context\ValidationContext;
use CleaRest\Validation\Context\MethodContext;
use CleaRest\Validation\Context\ObjectContext;

/**
 * Validates method arguments and object properties
 */
class Validator
{
    /**
     * @var ValidationRule[]
     */
    private static $rules = [];

    /**
     * Register default validation rules
     */
    public static function registerDefaultRules()
    {
        $defaultAnnotations = require __DIR__ . '/Rules/_default.php';
        foreach ($defaultAnnotations as $name => $class) {
            if (!isset(self::$rules[$name])) {
                self::registerRule($name, new $class);
            }
        }
    }

    /**
     * Register a validation rule to be used.
     * @param string $name available for @validate annotation
     * @param ValidationRule $rule
     */
    public static function registerRule($name, ValidationRule $rule)
    {
        self::$rules[strtolower($name)] = $rule;
    }

    /**
     * Returns rule instance by $name
     * @param $name
     * @return ValidationRule
     */
    public static function getRule($name)
    {
        if (isset(self::$rules[strtolower($name)])) {
            return self::$rules[strtolower($name)];
        }
        return null;
    }

    /**
     * Validate method arguments based on given metadata and returns list of violations
     * @param array $args
     * @param Method $method
     * @param MethodContext $context
     * @throws FrameworkException
     * @return Violation[]
     */
    public static function validateArguments(array $args, Method $method, MethodContext $context)
    {
        $context->method = $method->name;
        /** @var Validate[][] $validates */
        $validates = [];
        foreach ($method->annotations as $annotation) {
            if ($annotation instanceof Validate) {
                $validates[$annotation->parameter][] = $annotation;
            }
        }

        $index = 0;
        $violations = [];
        foreach ($method->parameters as $parameter) {
            $annotations = isset($validates[$parameter->name]) ? $validates[$parameter->name] : [];
            $value = isset($args[$index]) ? $args[$index] : $parameter->default;
            $index++;

            $context->parameter = $parameter->name;
            $violations = array_merge(
                $violations,
                self::validateEntry($value, $annotations, $parameter, $context)
            );
        }
        return $violations;
    }

    /**
     * Validates object properties and returns list of violations
     * @param $object
     * @param ValidationContext $parentContext
     * @return Violation[]
     */
    public static function validateObject($object, ValidationContext $parentContext = null)
    {
        if ($object instanceof UploadedFile) {
            return [];
        }

        $context = new ObjectContext();
        $context->class = get_class($object);
        if ($parentContext !== null) {
            $context->parent = $parentContext;
        }

        $metadata = MetadataStorage::getClassMetadata($context->class);
        $violations = [];
        foreach ($metadata->properties as $property) {
            if ($property->scope !== Property::SCOPE_PUBLIC) {
                continue;
            }

            $value = null;
            if (isset($object->{$property->name})) {
                $value = $object->{$property->name};
            } elseif ($property->hasDefault) {
                $value = $property->default;
            }

            $annotations = array_filter($property->annotations, function($annotation) {
                return $annotation instanceof Validate;
            });

            $context->property = $property->name;
            $propertyViolations = self::validateEntry($value, $annotations, $property, $context);
            $violations = array_merge($violations, $propertyViolations);
        }

        return $violations;
    }

    /**
     * @param mixed $value
     * @param Validate[] $annotations
     * @param Value $metadata
     * @param ValidationContext $context
     * @return Violation[]
     */
    private static function validateEntry($value, array $annotations, Value $metadata, ValidationContext $context) {
        $context = clone $context;
        /** @var Type[] $types */
        $types = is_array($metadata->type) ? $metadata->type : [$metadata->type];
        $typeViolations = [];
        foreach ($types as $type) {
            $typeViolations[$type->name] = [];
            if (!$type->isArray) {
                if (is_array($value) && $type->name !== 'mixed') {
                    $typeViolations[$type->name][] = new Violation("Invalid structure. Scalar expected", $value, $context);
                    break;
                }
                $typeViolations[$type->name] =  self::validateField($value, $annotations, $context, $metadata->enum);
            } else {
                if (!is_array($value)) {
                    if ($value === null) {
                        break;
                    }
                    $typeViolations[$type->name][] = new Violation("Invalid structure. Array expected", $value, $context);
                    break;
                }
                foreach ($value as $k => $v) {
                    $context->index = $k;
                    $typeViolations[$type->name] = array_merge(
                        $typeViolations[$type->name],
                        self::validateField($v, $annotations, $context, $metadata->enum)
                    );
                }
            }
            if (empty($typeViolations[$type->name])) {
                return [];
            }
        }
        $violations = [];
        foreach ($typeViolations as $v) {
            $violations = array_merge($violations, $v);
        }
        return $violations;
    }

    /**
     * @param mixed $value
     * @param Validate[] $annotations
     * @param ValidationContext $context
     * @param string $enumClass
     * @throws FrameworkException
     * @return Violation[]
     */
    private static function validateField($value, array $annotations, ValidationContext $context, $enumClass = null)
    {
        $context = clone $context;
        $violations = [];
        if (is_object($value) && !($value instanceof \DateTime)) {
            $violations = array_merge($violations, self::validateObject($value, $context));
        }
        if (!empty($annotations)) {
            foreach ($annotations as $validate) {
                $rule = self::getRule($validate->rule);
                if (!$rule->validate($value, $validate->args)) {
                    $violations[] = new Violation(
                        $rule->getErrorMessage($value, $validate->args),
                        $value,
                        $context
                    );
                }
            }
        }
        if (!self::validateEnum($value, $enumClass)) {
            $violations[] = new Violation("Value does not belong to enum", $value, $context);
        }

        return $violations;
    }

    private static function validateEnum($value, $class)
    {
        if ($class === null) {
            return true;
        }
        $reflection = new \ReflectionClass($class);
        $constants = $reflection->getConstants();
        return in_array($value, $constants);
    }

}