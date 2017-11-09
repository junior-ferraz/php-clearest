<?php
namespace CleaRest\Metadata;

use CleaRest\FrameworkException;
use CleaRest\Metadata\Annotations\Registry\Arg;
use CleaRest\Metadata\Annotations\Registry\Entry;
use CleaRest\Util\ArrayFunctions;

/**
 * Extend this class to create your custom annotations, then register it with the register($name, $class) method
 */
abstract class Annotation
{
    /**
     * @var Entry[]
     */
    private static $registry = [];

    /**
     * Registers default list of annotations
     */
    public static function registerDefault()
    {
        $defaultAnnotations = require __DIR__ . '/Annotations/Registry/_default.php';
        foreach ($defaultAnnotations as $name => $class) {
            if (!isset(self::$registry[$name])) {
                self::register($name, $class);
            }
        }
    }

    /**
     * Registers (or override) an annotation $name
     * @param string $name name of the annotation
     * @param string $class name of the class
     * @throws FrameworkException
     */
    public static function register($name, $class)
    {
        $name = strtolower($name);
        if (!class_exists($class) || !is_subclass_of($class, Annotation::class)) {
            throw new FrameworkException("Invalid class $class given for annotation @$name");
        }

        $entry = new Entry();
        $entry->class = $class;
        $entry->reflection = new \ReflectionClass($class);
        foreach ($entry->reflection->getConstructor()->getParameters() as $param) {
            $arg = new Arg();
            $arg->name = $param->getName();
            if ($param->isDefaultValueAvailable()) {
                $arg->hasDefaultValue = true;
                $arg->defaultValue = $param->getDefaultValue();
            }
            $entry->args[] = $arg;
        }

	    self::$registry[$name] = $entry;
    }

    /**
     * Creates an instance of annotation by $name given its $properties
     * @param string $name
     * @param array $properties
     * @return Annotation
     * @throws FrameworkException
     */
    public static function create($name, array $properties)
    {
        $entry = self::getEntry($name);

        $args = [];
        foreach ($entry->args as $index => $arg) {
            $value = null;
            if (isset($properties[$arg->name])) {
                $value = $properties[$arg->name];
                unset($properties[$arg->name]);
            } elseif (isset($properties[$index])) {
                $value = $properties[$index];
                unset($properties[$index]);
            } elseif ($arg->hasDefaultValue) {
                $value = $arg->defaultValue;
            } else {
                throw new FrameworkException("Parameter '{$arg->name}' for annotation @$name is mandatory");
            }
            $args[$index] = $value;
        }
        if (count($properties) > 0) {
            foreach ($properties as $value) {
                $args[] = $value;
            }
        }

        /** @var Annotation $annotation */
        $annotation = $entry->reflection->newInstanceArgs($args);
        foreach ($properties as $name => $value) {
            if (!is_string($name)) continue;
            $annotation->{$name} = $value;
        }

        return $annotation;
    }


    public function __toString()
    {
        $annotationName = 'UNREGISTERED';
        foreach (self::$registry as $name => $entry) {
            if ($entry->class == static::class) {
                $annotationName = $name;
                break;
            }
        }
        $properties = get_object_vars($this);
        return "Annotation::create('$annotationName', " . ArrayFunctions::toString($properties) . ")";
    }

    /**
     * @param string $name
     * @return Entry
     * @throws \Exception
     */
    private static function getEntry($name)
    {
        $name = strtolower($name);
        if (!isset(self::$registry[$name])) {
            throw new FrameworkException("Unknown annotation @$name");
        }
        return self::$registry[$name];
    }
}
