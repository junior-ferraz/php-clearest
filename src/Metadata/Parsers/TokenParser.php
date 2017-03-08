<?php

namespace CleaRest\Metadata\Parsers;

/**
 * Parses a class file and returns relevant information that the shity PHP reflection doesn't
 */
class TokenParser
{

  /**
   * Extracts all classes include with `use` clause and its alias as index of array
   * @param \ReflectionClass $clazz
   * @return string[]
   */
    public static function getUsedClasses(\ReflectionClass $clazz)
    {
        $content = file_get_contents($clazz->getFileName());
        $tokens = token_get_all($content);

        $uses = array();
        $className = null;
        $alias = null;
        $using = false;
        $hasAlias = false;
        foreach ($tokens as $token) {

            if (is_array($token)) {
                list($tokenType, $tokenValue) = $token;
            } else {
                $tokenType = null;
                $tokenValue = $token;
            }

            switch ($tokenType) {
                case T_WHITESPACE:
                    break;
                case T_USE:
                    $using = true;
                    break;
                case T_AS:
                    $hasAlias = true;
                    break;
                case T_STRING:
                case T_NS_SEPARATOR:
                    if (!$using) break;
                    if ($hasAlias) {
                        $alias = $tokenValue;
                    } else {
                        $alias = $tokenValue;
                        $className .= $tokenValue;
                    }
                    break;
                default:
                    if ($using) {
                        $uses[$alias] = $className;
                        $className = null;
                        $alias = null;
                        $using = false;
                        $hasAlias = false;
                    }
                    break;
            }
        }

        return $uses;
    }

    /**
     * Returns the doc comment text written above the constants of a class
     * @param \ReflectionClass $clazz
     * @return string[]
     */
    public static function getConstantDocs(\ReflectionClass $clazz)
    {
        $content = file_get_contents($clazz->getFileName());
        $tokens = token_get_all($content);

        $doc = null;
        $isConst = false;
        $constants = array();
        foreach ($tokens as $token) {
            if (is_array($token)) {
                list($tokenType, $tokenValue) = $token;
            } else {
                $tokenType = null;
                $tokenValue = $token;
            }

            switch ($tokenType) {
                // ignored tokens
                case T_WHITESPACE:
                case T_COMMENT:
                    break;
                case T_DOC_COMMENT:
                    $doc = $tokenValue;
                    break;
                case T_CONST:
                    $isConst = true;
                    break;
                case T_STRING:
                    if ($isConst) {
                        $constants[$tokenValue] = self::cleanDoc($doc);
                    }
                    $doc = null;
                    $isConst = false;
                    break;
                // all other tokens reset the parser
                default:
                    $doc = null;
                    $isConst = false;
                    break;
            }
        }
        return $constants;
    }

    private static function cleanDoc($doc)
    {
        if ($doc === null) {
            return null;
        }
        $result = null;
        $lines = preg_split('/\R/', $doc);
        foreach($lines as $line) {
            $line = trim($line, "/* \t\x0B\0");
            if ($line === '') {
                continue;
            }

            if ($result != null) {
                $result .= "\n";
            }
            $result .= $line;
        }
        return $result;
    }

}
