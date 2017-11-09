<?php
namespace CleaRest\Util;

/**
 * Array util functions
 */
class ArrayFunctions
{
    const LINE_BREAK = "\n";
    const TABULATION = "\t";
    private static $tabLevel = 0;

    /**
     * Returns true of array has only integer keys
     * @param array $array
     * @return bool
     */
    public static function isIndexed(array $array)
    {
        foreach ($array as $key => $value) {
            if (!is_int($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns true if array has only string keys
     * @param array $array
     * @return bool
     */
    public static function isAssociative(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Converts array into tabulated string
     *
     * @param array $array
     * @return string
     */
    public static function toString(array $array) {
        $tab    = str_repeat(self::TABULATION, self::$tabLevel);

        $str = "[" . (count($array) ? self::LINE_BREAK : '' );
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                $key = self::castString($key);
            }
            $str .= self::TABULATION . $tab . "$key => ";
            if (is_array($value)) {
                self::$tabLevel++;
                $str .= self::toString($value);
                self::$tabLevel--;
            } else {
                if ($value === null) {
                    $value = 'null';
                } elseif(is_string($value)) {
                    $value = self::castString($value);
                } elseif (is_bool($value)) {
                    $value = ($value ? 'true' : 'false');
                } elseif (is_object($value)) {
                    if (method_exists($value, '__toString')) {
                        $value = str_replace("\n", "\n$tab", "$value");
                    } else {
                        $value = 'null';
                    }
                }
                $str .= $value;
            }
            $str .= ',' . self::LINE_BREAK;
        }
        $str .= (count($array) ? $tab : '' ) . ']';
        return $str;
    }

    private static function castString($str) {
        return '"' . str_replace (
                array('\\'  , '"' , "\n",  '$'),
                array('\\\\','\\"', "\\n", '\$'),
                $str)
            . '"';
    }
}
