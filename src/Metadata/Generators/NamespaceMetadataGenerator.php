<?php
namespace CleaRest\Metadata\Generators;

use CleaRest\Api\Data\PlainObject;
use CleaRest\Metadata\ConsoleTool;
use CleaRest\Metadata\Descriptors\Clazz;
use CleaRest\Metadata\MetadataStorage;
use CleaRest\Services\Service;

/**
 * Generates metadata from classes in namespace
 */
class NamespaceMetadataGenerator
{
    /**
     * Only classes that implement those interfaces or extend it will be considered if --light option is set to true
     * @var string[]
     */
    private static $classesInLightGeneration = [Service::class, PlainObject::class];

    /**
     * @param string $namespace only classes under this namespace are considered
     * @param string $srcPath path to look for classes
     * @param bool $light if true only services and plain objects will be considered
     * @param string $includeExtension file extension that will be open to find classes
     * @return Clazz[]
     */
    public static function generate($namespace, $srcPath, $light = false, $includeExtension = MetadataStorage::FILE_EXTENSION)
    {
        if ($srcPath !== null) {
            MetadataStorage::setMetadataFolder(MetadataStorage::guessMetadataFolder($srcPath));
        }

        ob_start();
        $files = self::getAllFiles($srcPath, $includeExtension);
        foreach ($files as $file) {
            include_once $file;
        }
        ob_end_clean();

        $classes = array_merge(get_declared_classes(), get_declared_interfaces());
        $includedClasses = 0;
        $namespaceLen = strlen($namespace);
        $classesMetadata = [];
        foreach ($classes as $class) {
            if (substr($class, 0, $namespaceLen) == $namespace) {
                $reflection = new \ReflectionClass($class);
                if ($light) {
                    $isConsidered = false;
                    foreach (self::$classesInLightGeneration as $type) {
                        if ($reflection->isSubclassOf($type)) {
                            $isConsidered = true;
                            break;
                        }
                    }
                    if (!$isConsidered) {
                        continue;
                    }
                }
                $classesMetadata[$class] = ClassMetadataGenerator::getMetadata($reflection);
                $includedClasses++;
            }
        }
        ConsoleTool::setNumObjects($includedClasses);

        return $classesMetadata;
    }

    /**
     * @param string $path
     * @param string $extension
     * @return string[]
     */
    private static function getAllFiles($path, $extension)
    {
        $extLen = strlen($extension);
        $files = [];
        $entries = scandir($path);
        foreach ($entries as $entry) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $entry;
            if (!in_array($entry, ['.','..']) && is_dir($fullPath)) {
                $files = array_merge($files, self::getAllFiles($fullPath, $extension));
            } elseif (substr($entry, -$extLen) == $extension) {
                $files[] = $fullPath;
            }
        }
        return $files;
    }
}
