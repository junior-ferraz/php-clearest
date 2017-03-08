<?php
namespace CleaRest\Metadata;

use CleaRest\Framework;
use CleaRest\FrameworkException;
use CleaRest\Metadata\Descriptors\Clazz;

/**
 * Saves and loads metadata information
 */
class MetadataStorage
{
    const METADATA_SUBFOLDER = 'metadata/';
    const FILE_EXTENSION = '.php';
    const FOLDER_MODE = 0777;

    /**
     * @var string
     */
    private static $metadataFolder;

    /**
     * Where can the metadata files be saved and loaded?
     * @param string $path
     */
    public static function setMetadataFolder($path)
    {
        if (!file_exists($path)) {
            mkdir($path, self::FOLDER_MODE, true);
        }
        self::$metadataFolder = $path;
    }

    /**
     * Where can the metadata files be saved and loaded?
     * @return string
     */
    public static function getMetadataFolder()
    {
        if (self::$metadataFolder === null) {
            self::setMetadataFolder(self::guessMetadataFolder());
        }
        return self::$metadataFolder;
    }

    /**
     * Guesses the metadata folder: It's "metadata" folder in the up most project root
     * @param string $startingPath
     * @return string
     */
    public static function guessMetadataFolder($startingPath = null)
    {
        return Framework::guessRootFolder($startingPath) . DIRECTORY_SEPARATOR . self::METADATA_SUBFOLDER;
    }

    /**
     * Loads class metadata
     * @param string|object $class
     * @return Clazz
     * @throws FrameworkException if metadata not found
     */
    public static function getClassMetadata($class, $throwException = true)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $file = self::getMetadataFolder()
            . str_replace('\\', DIRECTORY_SEPARATOR, $class)
            . self::FILE_EXTENSION;
        if (!file_exists($file)) {
            if ($throwException) {
                throw new FrameworkException("No metadata file $file found for class $class");
            }
            return null;
        }
        return include $file;
    }

    /**
     * Saves class metadata file
     * @param Clazz $class
     */
    public static function saveClassMetadata(Clazz $class)
    {
        $fileName = self::getMetadataFolder()
            . str_replace('\\', DIRECTORY_SEPARATOR, $class->name)
            . self::FILE_EXTENSION;
        $dirName = dirname($fileName);
        if (!file_exists($dirName)) {
            mkdir($dirName, self::FOLDER_MODE, true);
        }
        $content = "<?php\nuse " . Annotation::class . ";\nreturn $class;\n";
        file_put_contents($fileName, $content);
    }
}

