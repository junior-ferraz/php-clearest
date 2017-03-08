<?php
namespace CleaRest\Metadata\Checkers;

use CleaRest\Api\Data\PlainObject;
use CleaRest\Api\Data\ScalarConverter;
use CleaRest\Metadata\ConsoleTool;
use CleaRest\Metadata\Descriptors\Clazz;

/**
 * Checks the validity of classes marked with PlainObject interface.
 * Those classes must have only scalar types or PlainObjects as properties
 */
class PlainObjectsChecker
{
    /**
     * Check properties from PlainObject classes
     * @param Clazz[] $classesMetadata
     */
    public static function checkClasses(array &$classesMetadata)
    {
        foreach ($classesMetadata as &$class) {
            if (!$class->isInterface && in_array(PlainObject::class, $class->implements)) {
                $invalidProperties = [];
                foreach ($class->properties as $property) {
                    if ($property->type === null) {
                        continue;
                    }
                    $types = is_array($property->type) ? $property->type : [$property->type];
                    foreach ($types as $type) {
                        $isPlainScalar = ScalarConverter::isAllowedType($type->name);
                        $isPlainObject = is_subclass_of($type->name, PlainObject::class);
                        if (!$isPlainScalar && !$isPlainObject) {
                            $invalidProperties[] = $property->name;
                        }
                    }
                }
                $class->isPlainObject = empty($invalidProperties);
                if (!empty($invalidProperties)) {
                    ConsoleTool::addError(
                        "The class {$class->name} is marked with PlainObject interface but has invalid properties: " .
                        implode(", ", $invalidProperties) . ". " .
                        "A PlainObject can only contain properties as native scalars, \\DateTime or other PlainObjects."
                    );
                }
            }
        }
    }
}