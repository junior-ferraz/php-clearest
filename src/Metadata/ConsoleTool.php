<?php
namespace CleaRest\Metadata;


use CleaRest\Framework;

/**
 * Helper class for generating metadata via console call
 */
class ConsoleTool
{
    /**
     * @var string[]
     */
    private static $errors = [];
    /**
     * @var string[]
     */
    private static $warnings = [];
    /**
     * @var int
     */
    private static $numObjects = 0;

    /**
     * Adds a critical error
     * @param string $text
     */
    public static function addError($text)
    {
        self::$errors[] = $text;
    }

    /**
     * Adds a warning
     * @param string $text
     */
    public static function addWarning($text)
    {
        self::$warnings[] = $text;
    }

    /**
     * Returns all critical errors
     * @return string[]
     */
    public static function getErrors()
    {
        return self::$errors;
    }

    /**
     * Returns all warnings
     * @return string[]
     */
    public static function getWarnings()
    {
        return self::$warnings;
    }

    /**
     * Sets number of analyzed objects
     * @param int $num
     */
    public static function setNumObjects($num)
    {
        self::$numObjects = $num;
    }

    /**
     * Runs console diagnostic
     * @param string[] $args
     */
    public static function run(array $args = null)
    {
        if ($args == null) {
            global $argv;
            $args = $argv;
        }
        if (count($args) < 3) {
            self::showDocumentation($args);
        }

        $path = null;
        $namespace = null;
        $light = true;
        $include = null;
        $outFolder = null;

        $index = 0;
        foreach ($args as $key => $param) {
            if (substr($param, 0, 2) == '--') {
                $split = explode('=', strtolower($param));
                $flag = $split[0];
                switch (substr($flag, 2)) {
                    case 'everything':
                        $light = false;
                        break;
                    case 'include':
                        $include = $split[1];
                        break;
                    case 'out-folder':
                        $outFolder = $split[1];
                        break;
                    default:
                        self::showError("Unknown flag $split[0] given.");
                        self::showDocumentation($args);
                }
            } elseif ($index == 0) {
                $index++;
            } elseif ($index == 1) {
                $path = $param;
                $index++;
            } elseif ($index == 2) {
                $namespace = $param;
                $index++;
            } else {
                self::showError("Unknown parameter $param.", false);
                self::showDocumentation($args);
            }
        }

        if (!file_exists($path)) {
            self::showError("Directory $path not found.");
        }

        if ($namespace == null) {
            self::showError("Namespace not defined.");
        }

        if ($include !== null) {
            if (!file_exists($include)) {
                self::showError("Include file $include not found.");
            }
            include_once $include;
        }

        if ($outFolder !== null) {
            MetadataStorage::setMetadataFolder($outFolder);
        }

        print "Generating metadata for:\n";
        print " - Framework classes\n";
        $frameworkClasses = self::generateFrameworkServicesMetadata();

        print " - Classes in namespace $namespace\n";
        $classes = Generators\NamespaceMetadataGenerator::generate($namespace, $path, $light);

        if (empty($classes)) {
            self::showError("No objects found for namespace $namespace.");
        }

        $classes = array_merge($frameworkClasses, $classes);

        print " - Services index\n";
        Generators\ServicesIndexGenerator::generate($classes);
        print " - Capabilities index\n";
        $capabilities = Generators\CapabilitiesIndexGenerator::generate($classes);

        print "Checking consistency for:\n";
        print " - Type hinting\n";
        Checkers\TypeChecker::checkClasses($classes);
        print " - Annotations\n";
        Checkers\AnnotationsChecker::checkClasses($classes, $capabilities);
        print " - Plain Objects\n";
        Checkers\PlainObjectsChecker::checkClasses($classes);

        if (!empty(self::getWarnings())) {
            print "\n";
            self::showError(count(self::getWarnings()) . " warnings found:", false);
            foreach (self::getWarnings() as $warning) {
                print " - $warning\n";
            }
            print "\n";
        }

        if (empty(self::getErrors())) {
            print "Saving metadata in files...\n";
            foreach ($classes as $class) {
                MetadataStorage::saveClassMetadata($class);
            }
            Generators\CapabilitiesIndexGenerator::saveIndex($capabilities);
            print "\n\e[32mMetadata generation successful:\e[0m\n" .
                "Number of objects: " . self::$numObjects . "\n" .
                "Folder: " . realpath(MetadataStorage::getMetadataFolder()) . "\n";
        } else {
            $count = count(self::$errors);
            self::showError("Metadata generation failed. Issues found: $count", false);
            foreach (self::getErrors() as $error) {
                print "  - $error\n";
            }
            print "No metadata generated.\n";
            exit(1);
        }
    }

    private static function showError($message, $exit = true)
    {
        print "\e[31m$message\e[0m\n";
        if ($exit) {
            exit;
        }
    }

    private static function showDocumentation(array $argv)
    {
        print "Set these parameters to generate the objects metadata:
    1. Source folder  (where the classes are located)
    2. Root namespace (only classes under this namespace will be considered)
Flags (optional)
    --out-folde=<path>r: Path where objects metadata files will be saved.
                         By default it's the \"metadata\" folder in the root level of current project
    --everything:        all classes are considered (if not present, only services and plain objects are considered)
    --include=<file>:    File to include before generation.
                         This is specially necessary when you register custom components such as:
                           - Annotations
                           - Validation rules
Exemple:
    php $argv[0] ./src my\\namespace --include=./my_bootstrap.php --everything 
";
        exit;
    }

    private static function generateFrameworkServicesMetadata()
    {
        $namespace = 'CleaRest\\Services\\Util';
        $path = Framework::getSourceFolder() . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . 'Util';
        return Generators\NamespaceMetadataGenerator::generate($namespace, $path, true);
    }
}
