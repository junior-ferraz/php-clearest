<?php
namespace CleaRest\Services;

use CleaRest\Api\Request;
use CleaRest\Capabilities\CapabilitiesFactory;
use CleaRest\FrameworkException;
use CleaRest\Metadata\Descriptors\Clazz;
use CleaRest\Metadata\MetadataStorage;

/**
 * Creates services instances based on given interface
 */
class ServicesFactory
{
    /**
     * Returns service instance based on given $interface and $version.
     * If $version is null, returns the highest version that suits the $request capabilities
     * @param string $interfaceName
     * @param Request $request
     * @param null $version
     * @return Service
     * @throws FrameworkException
     */
    public static function getServiceInstance($interfaceName, Request $request, $version = null)
    {
        $service = MetadataStorage::getClassMetadata($interfaceName);
        if (empty($service->versions)) {
            throw new FrameworkException("No service $interfaceName found (is the index up to date?)");
        }

        $class = self::getClassFromVersion($version, $service, $request);

        if ($request->getDependencyContainer()->has($class)) {
            $instance = $request->getDependencyContainer()->get($class);
            return ServiceProxy::create($instance);
        }

        /** @var Service $instance */
        $instance = new $class;
        DependencyInjector::inject($instance, $request);

        return ServiceProxy::create($instance);
    }

    /**
     * Gets class to be instantiated based on the version given.
     * When no version is given, it picks up the highest version that satisfy capabilities assertions
     * @param string|int $version
     * @param Clazz $service
     * @param Request $request
     * @throws FrameworkException
     * @return string
     */
    private static function getClassFromVersion($version, Clazz $service, Request $request)
    {
        if ($version === null || is_bool($version)) {
            while (count($service->versions) > 0) {
                $instance = array_pop($service->versions);
                if ($instance->capability === null) {
                    return $instance->class;
                }
                $capability = CapabilitiesFactory::get($instance->capability, $request);
                if ($capability->check()) {
                    return $instance->class;
                }
            }
            $version = "any version";
        } elseif (is_numeric($version)) {
            if (isset($service->versions[$version])) {
                $instance = $service->versions[$version];
                if ($instance->capability === null) {
                    return $instance->class;
                }
                $capability = CapabilitiesFactory::get($instance->capability, $request);
                if ($capability->check()) {
                    return $instance->class;
                }
            }
            $version = "version #$version";
        } elseif (is_string($version)) {
            foreach ($service->versions as $v) {
                if ($v->name == $version) {
                    if ($v->capability === null) {
                        return $v->class;
                    }
                    $capability = CapabilitiesFactory::get($v->capability, $request);
                    if ($capability->check()) {
                        return $v->class;
                    }
                }
            }
            $version = "version '$version'";
        }
        throw new FrameworkException(
            "Version not found or request does not have rights to use $version of service {$service->name}"
        );
    }
}
