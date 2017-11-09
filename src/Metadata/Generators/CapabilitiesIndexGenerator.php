<?php
namespace CleaRest\Metadata\Generators;

use CleaRest\Capabilities\Capability;
use CleaRest\FrameworkException;
use CleaRest\Metadata\Annotations\Alias;
use CleaRest\Metadata\ConsoleTool;
use CleaRest\Metadata\Descriptors\Clazz;
use CleaRest\Metadata\MetadataStorage;

/**
 * Creates an index for capabilites
 */
class CapabilitiesIndexGenerator
{
    const INDEX_FILE = 'CleaRest\\Capabilities';

    /**
     * Generates capabilities index
     * @param Clazz[] $classesMetadata
     * @return string[]
     */
    public static function generate(array $classesMetadata)
    {
        $index = [];
        foreach ($classesMetadata as $class) {
            if (in_array(Capability::class, $class->implements)) {
                $alias = null;
                foreach ($class->annotations as $annotation) {
                    if ($annotation instanceof Alias) {
                        $alias = $annotation;
                        break;
                    }
                }
                if ($alias === null) {
                    ConsoleTool::addError("Capability {$class->name} does not have @alias annotation");
                    continue;
                }
                if (isset($index[$alias->name])) {
                    ConsoleTool::addError(
                        "Capability alias '{$alias->name}' defined in class {$class->name} " .
                        "is already user by {$index[$alias->name]}"
                    );
                    continue;
                }
                $index[$alias->name] = $class->name;
            }
        }
        return $index;
    }

    /**
     * Saves the index into a file
     * @param string[] $index
     */
    public static function saveIndex(array $index)
    {
        $fileName = self::getIndexFileName();
        $dirName = dirname($fileName);
        if (!file_exists($dirName)) {
            mkdir($dirName, MetadataStorage::FOLDER_MODE, true);
        }
        $content = [];
        foreach ($index as $key => $value) {
            $content[] = "\t'$key' => '$value'";
        }
        $content = "<?php\nreturn [\n" . implode(",\n", $content) . "\n];\n";
        file_put_contents($fileName, $content);
    }

    /**
     * Loads the index
     * @return string[]
     * @throws FrameworkException
     */
    public static function loadIndex()
    {
        $fileName = self::getIndexFileName();
        if (!file_exists($fileName)) {
            throw new FrameworkException("No capability index found. Is the metadata up to date?");
        }
        return include $fileName;
    }

    private static function getIndexFileName()
    {
        return MetadataStorage::getMetadataFolder()
            . str_replace('\\', DIRECTORY_SEPARATOR, self::INDEX_FILE)
            . MetadataStorage::FILE_EXTENSION;
    }
}