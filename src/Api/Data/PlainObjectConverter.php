<?php
namespace CleaRest\Api\Data;

use CleaRest\Api\Exceptions\InternalException;
use CleaRest\Api\Exceptions\RequestException;
use CleaRest\Metadata\Descriptors\Clazz;
use CleaRest\Metadata\MetadataStorage;

/**
 * Helper class to create PlainObjects out of an array (with its class metadata) and vice-versa
 */
class PlainObjectConverter
{

    /**
     * Converts array into PlainObject based on metadata
     *
     * @param mixed[] $array array representation of object
     * @param Clazz $class PlainObject metadata
     * @param string $parentField used for throwing verbose exceptions when attributes fail
     * @return mixed
     * @throws InternalException
     * @throws RequestException
     */
    public static function toObject(array $array, Clazz $class, $parentField = '')
    {
        // TODO: check if abstract, create instance of child by descriptor
        // TODO: error handling with neasted properties

        if (!$class->isPlainObject) {
            throw new InternalException("The class {$class->name} is not a valid PlainObject");
        }

        $object = new $class->name;
        foreach ($class->properties as $property) {
            if (!array_key_exists($property->name, $array)) {
                if ($property->hasDefault) {
                    $object->{$property->name} = $property->default;
                } else {
                    unset($object->{$property->name});
                }
            } elseif($array[$property->name] === null) {
                $object->{$property->name} = null;
            } else {
                $fieldName = $parentField . '.' . $property->name;
                $value = $array[$property->name];
                $types = is_array($property->type) ? $property->type : [$property->type];
                $convertedValue = null;
                foreach ($types as $type) {
                    $convertedValue = ValueConverter::convertRequestValue($value, $type, $fieldName);
                    if ($convertedValue !== null) {
                        break;
                    }
                }
                if ($convertedValue === null) {
                    throw new RequestException(
                        "It was not possible to convert field $fieldName into expected type",
                        RequestException::INVALID_FIELD,
                        ['field' => $fieldName]
                    );
                }
                $object->{$property->name} = $convertedValue;
            }
        }

        return $object;
    }

    /**
     * Converts PlainObject into array
     * @param PlainObject $object
     * @param bool $filterNullValues
     * @return array
     */
    public static function toArray(PlainObject $object, $filterNullValues = true)
    {
        $properties = get_object_vars($object);

        $array = [];
        foreach ($properties as $property => $value) {
            if ($value === null && $filterNullValues) {
                continue;
            }
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $array[$property][$k] = self::valueToArray($v);
                }
            } else {
                $array[$property] = self::valueToArray($value);
            }
        }

        return $array;
    }

    /**
     * Creates a new instance of $class and copies properties of it from $object
     *
     * @param PlainObject $object to convert
     * @param string|Clazz $class destiny type
     *
     * @return PlainObject
     */
    public static function cast(PlainObject $object, $class)
    {
        if (!$class instanceof Clazz) {
            $class = MetadataStorage::getClassMetadata($class);
        }
        return self::toObject(self::toArray($object), $class);
    }

    /**
     * Converts a value to its array representation
     *
     * @param mixed $value
     * @return array|string
     * @throws InternalException
     */
    private static function valueToArray($value) {
        if ($value instanceof PlainObject) {
            return self::toArray($value);
        } elseif ($value instanceof \DateTime) {
            return ScalarConverter::dateToString($value);
        } elseif (is_scalar($value)) {
            return $value;
        } else {
            throw new InternalException("Only scalars and PlainObjects can be returned by an API service");
        }
    }

}