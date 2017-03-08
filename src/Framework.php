<?php
namespace CleaRest;

use CleaRest\Api\ContentTypeRegistry;
use CleaRest\Metadata\Annotation;
use CleaRest\Validation\Validator;

/**
 * Main start class
 */
class Framework
{
    /**
     * Register default classes such as annotations, validation rules and content-type enconders
     */
    public static function start() {
        Annotation::registerDefault();
        Validator::registerDefaultRules();
        ContentTypeRegistry::registerDefault();
    }

    /**
     * Returns framework source folder
     * @return string
     */
    public static function getSourceFolder()
    {
        return dirname(__FILE__);
    }

    /**
     * Guesses root project folder (expects it to be the upper first one where the 'vendor' folder is present).
     * Throws an exception if it can't find.
     * @param null $startingPath
     * @return null|string
     * @throws FrameworkException
     */
    public static function guessRootFolder($startingPath = null)
    {
        if ($startingPath === null) {
            $startingPath = self::getSourceFolder();
        }
        $path = $startingPath;
        while (!is_dir($path . DIRECTORY_SEPARATOR . 'vendor')) {
            $newPath = dirname($path);
            if ($path == $newPath) {
                throw new FrameworkException('Cannot find project root');
            }
            $path = $newPath;
        }
        return $path;
    }
}