<?php

namespace CleaRest\Api\Data;

use CleaRest\Api\Exceptions\InternalException;
use CleaRest\Api\Exceptions\RequestException;
use CleaRest\Metadata\Descriptors\Type;
use CleaRest\Metadata\MetadataStorage;
use CleaRest\Util\ArrayFunctions;

/**
 * Helper class for converting request data into expected types
 */
class ValueConverter
{
    /**
     * Convert request data into expected type
     * @param mixed $value
     * @param Type $type
     * @param string $fieldName
     * @return array|bool|PlainObject|\DateTime|float|int|mixed|null|string
     * @throws RequestException
     */
    public static function convertRequestValue($value, Type $type, $fieldName)
    {
        if ($type->isArray) {
            if (!is_array($value) || !ArrayFunctions::isIndexed($value)) {
                throw new RequestException(
                    "The field $fieldName has invalid structure: array expected.",
                    RequestException::INVALID_FIELD,
                    ['field' => $fieldName]
                );
            }
            return self::convertArrayOfValues($value, $type->name, $fieldName);
        } else {
            if (is_array($value) && !ArrayFunctions::isAssociative($value) && $type->name !== 'mixed') {
                throw new RequestException(
                    "The field $fieldName has invalid structure: scalar expected.",
                    RequestException::INVALID_FIELD,
                    ['field' => $fieldName]
                );
            }
            return self::convertSingleValue($value, $type->name, $fieldName);
        }
    }

    /**
     * Converts value into response valid output
     * @param mixed $value
     * @return mixed
     * @throws InternalException
     */
    public static function convertResponseValue($value)
    {
        if ($value === null) {
            return null;
        } elseif (is_array($value)) {
            $values = [];
            foreach ($value as $k => $v) {
                $values[$k] = self::convertResponseValue($v);
            }
            return $values;
        } elseif ($value instanceof PlainObject) {
            return PlainObjectConverter::toArray($value);
        } elseif ($value instanceof \DateTime) {
            return ScalarConverter::dateToString($value);
        } elseif (is_scalar($value)) {
            return $value;
        }
        throw new InternalException("Unexpected value returned by the service");
    }

    /**
     * Converts value into array of entries
     * @param mixed[] $values
     * @param string $typeName
     * @param string $fieldName
     * @return array|null
     */
    private static function convertArrayOfValues(array $values, $typeName, $fieldName)
    {
        if (empty($values)) {
            return [];
        }
        $convertedValues = [];
        foreach ($values as $k => $v) {
            $convertedValue = self::convertSingleValue($v, $typeName, $fieldName . "[$k]");
            if ($convertedValue !== null) {
                $convertedValues[$k] = $convertedValue;
            }
        }
        return count($convertedValues) > 0 ? $convertedValues : null;
    }

    /**
     * Converts value into single entry
     * @param mixed $value
     * @param string $typeName
     * @param string $fieldName
     * @return bool|\DateTime|float|int|mixed|null|string|PlainObject
     */
    private static function convertSingleValue($value, $typeName, $fieldName)
    {
        if ($value === null) {
            return null;
        } elseif (ScalarConverter::isAllowedType($typeName)) {
            return ScalarConverter::convertTo($value, $typeName);
        } else {
            $propertyClass = MetadataStorage::getClassMetadata($typeName);
            if ($propertyClass->isPlainObject) {
                return PlainObjectConverter::toObject($value, $propertyClass, $fieldName);
            } else {
                throw new InternalException("Cannot convert $typeName because it is not a PlainObject");
            }
        }
    }
}