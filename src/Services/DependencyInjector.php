<?php
namespace CleaRest\Services;

use CleaRest\Api\Request;
use CleaRest\Metadata\Annotations\Inject;
use CleaRest\Metadata\MetadataStorage;

class DependencyInjector
{
    /**
     * Injects dependencies into service $instance
     * @param Service $instance
     * @param Request $request
     */
    public static function inject(Service $instance, Request $request)
    {
        $instance->setRequest($request);
        $request->getDependencyContainer()->set($instance);

        // injecting dependencies
        $metadata = MetadataStorage::getClassMetadata($instance);
        foreach ($metadata->properties as $property) {
            foreach ($property->annotations as $annotation) {
                if ($annotation instanceof Inject) {
                    $dependency = ServicesFactory::getServiceInstance(
                        $property->type->name,
                        $request,
                        $annotation->version
                    );
                    $instance->setDependency($property->name, $dependency);
                    break;
                }
            }
        }

        $instance->afterInitialize();
    }
}
