<?php

namespace CleaRest\Api\Data;

/**
 * Helper class for scalar conversion
 */
class ScalarConverter
{
    private static $dateFormat = \DateTime::ISO8601;

    private static $allowedTypes = [
        'mixed', 'string', 'bool', 'int', 'long', 'float', 'real', 'double', 'numeric', \DateTime::class
    ];

    private static $booleanRepresentations = [
        '1'    => true, '0'     => false,
        'true' => true, 'false' => false,
        'on'   => true, 'off'   => false,
        'yes'  => true, 'no'    => false,
    ];

    /**
     * Checks if given type is allowed as an API scalar one
     * @param string $type
     * @return bool
     */
    public static function isAllowedType($type)
    {
        return in_array($type, self::$allowedTypes);
    }

    /**
     * returns format used for conversion from/into DateTime
     * @return string
     */
    public static function getDateFormat()
    {
        return self::$dateFormat;
    }

    /**
     * Sets format used for conversion from/into DateTime
     * @param $format
     */
    public static function setDateFormat($format)
    {
        self::$dateFormat = $format;
    }

    /**
     * Converts $value into $type
     *
     * @param mixed $value
     * @param string $type
     * @return bool|\DateTime|float|int|mixed|null|string
     */
    public static function convertTo($value, $type)
    {
        switch ($type) {
            case 'mixed':
                return $value;
            case 'string':
                return self::toString($value);
            case 'bool':
                return self::toBool($value);
            case 'int':
            case 'long':
                return self::toInt($value);
            case 'float':
            case 'real':
            case 'double':
            case 'numeric':
                return self::toReal($value);
            case \DateTime::class:
                return self::toDate($value);
        }
        return null;
    }

    /**
     * Converts $value into string
     * @param mixed $value
     * @return null|string
     */
    public static function toString($value)
    {
        try {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }
            return "$value";
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * Converts $value into integer
     * @param mixed $value
     * @return int|null
     */
    public static function toInt($value)
    {
        if (!is_numeric($value)) {
            return null;
        }
        return intval($value);
    }

    /**
     * Converts $value into float
     * @param mixed $value
     * @return float|null
     */
    public static function toReal($value)
    {
        if (!is_numeric($value)) {
            return null;
        }
        return (real)$value;
    }

    /**
     * Converts $value into boolean
     * @param mixed $value
     * @return mixed|null
     */
    public static function toBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        $key = strtolower(self::toString("$value"));
        if (!array_key_exists($key, self::$booleanRepresentations)) {
            return null;
        }
        return self::$booleanRepresentations[$key];
    }

    /**
     * Converts $value into DateTime
     * @param string|int $value
     * @return bool|\DateTime|null
     */
    public static function toDate($value)
    {
        if ($value instanceof \DateTime) {
            return $value;
        }
        if (is_int($value)) {
            $date = new \DateTime();
            $date->setTimestamp($value);
            return $date;
        }
        if (is_string($value)) {
            $converted = \DateTime::createFromFormat(self::getDateFormat(), $value);
            return $converted === false ? null : $converted;
        }
        return null;
    }

    /**
     * Converts DateTime into string
     * @param \DateTime $date
     * @return string
     */
    public static function dateToString(\DateTime $date)
    {
        return $date->format(self::$dateFormat);
    }
}
