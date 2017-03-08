<?php
namespace CleaRest\Metadata\Generators;

use CleaRest\Metadata\Descriptor;
use CleaRest\Metadata\Descriptors\Clazz;
use CleaRest\Metadata\Descriptors\Method;
use CleaRest\Metadata\Descriptors\Parameter;
use CleaRest\Metadata\Descriptors\Property;
use CleaRest\Metadata\Parsers\DocCommentParser;
use CleaRest\Metadata\Parsers\TokenParser;

/**
 * Generates metadata information for a class, its methods and properties
 */
class ClassMetadataGenerator
{
    /**
     * Generates class metadata
     * @param \ReflectionClass $class
     * @return Clazz
     */
	public static function getMetadata(\ReflectionClass $class)
    {
        $metadata = self::extractDefaultMetadata($class, new Clazz());

        $metadata->namespace = $class->getNamespaceName();
        $metadata->shortName = $class->getShortName();
        $metadata->isAbstract = $class->isAbstract();
        $metadata->isInterface = $class->isInterface();
        $metadata->extends = $class->getParentClass() ? $class->getParentClass()->getName() : null;
        $metadata->implements = $class->getInterfaceNames();
        $metadata->constants = $class->getConstants();
        $metadata->uses = TokenParser::getUsedClasses($class);

        foreach ($class->getMethods() as $method) {
            $metadata->methods[$method->getName()] = self::getMethodMetadata($method, $metadata);
        }

        $defaultProperties = $class->getDefaultProperties();
        foreach ($class->getProperties() as $property) {
            $propertyMetadata = self::getPropertyMetadata($property, $metadata);
            if (isset($defaultProperties[$propertyMetadata->name])) {
                $propertyMetadata->hasDefault = true;
                $propertyMetadata->default = $defaultProperties[$propertyMetadata->name];
            }
            $metadata->properties[$property->getName()] = $propertyMetadata;
        }

        return $metadata;
    }

    /**
     * @param \ReflectionMethod $method
     * @param Clazz $class
     * @return Method
     */
    private static function getMethodMetadata(\ReflectionMethod $method, Clazz $class)
    {
        $metadata = self::extractDefaultMetadata($method, new Method());
        $metadata->scope = $method->isPublic() ? Descriptor::SCOPE_PUBLIC
            : ($method->isProtected() ? Descriptor::SCOPE_PROTECTED : Descriptor::SCOPE_PRIVATE);

        foreach ($method->getParameters() as $parameter) {
            $param = new Parameter();
            $param->name = $parameter->getName();

            if ($parameter->isDefaultValueAvailable()) {
                $param->hasDefault = true;
                $param->default = $parameter->getDefaultValue();
            }

            $metadata->parameters[$parameter->getName()] = $param;
        }

        return $metadata;
    }

    /**
     * @param \ReflectionProperty $property
     * @param Clazz $class
     * @return Property
     */
    private static function getPropertyMetadata(\ReflectionProperty $property, Clazz $class)
    {
        $metadata = self::extractDefaultMetadata($property, new Property());
        $metadata->scope = $property->isPublic() ? Descriptor::SCOPE_PUBLIC
            : ($property->isProtected() ? Descriptor::SCOPE_PROTECTED : Descriptor::SCOPE_PRIVATE);


        return $metadata;
    }

    /**
     * @param \ReflectionClass|\ReflectionMethod|\ReflectionProperty $reflection
     * @param Descriptor $metadata
     * @return Descriptor|Clazz|Method|Property
     */
    private static function extractDefaultMetadata($reflection, Descriptor $metadata)
    {
        $metadata->name = $reflection->getName();

        $docComment = $reflection->getDocComment();
        if (empty($docComment)) {
            return $metadata;
        }

        $origin = $metadata->name;
        if (! $reflection instanceof \ReflectionClass) {
            $origin = $reflection->getDeclaringClass()->getName() .  "." . $origin;
            if ($reflection instanceof \ReflectionMethod) {
                $origin = "method $origin";
            } elseif ($reflection instanceof \ReflectionProperty) {
                $origin = "property $origin";
            }
        }

        $metadata->description = DocCommentParser::getText($docComment);
        $metadata->annotations = DocCommentParser::getAnnotations($docComment, $origin);

        return $metadata;
    }
}
