<?php
namespace CleaRest\Capabilities;


use CleaRest\Api\Request;
use CleaRest\FrameworkException;
use CleaRest\Metadata\Generators\CapabilitiesIndexGenerator;
use CleaRest\Services\DependencyInjector;
use CleaRest\Services\Service;

class CapabilitiesFactory
{
    private static $index;

    /**
     * @param string $capabilityName
     * @param Request $request
     * @return Capability
     * @throws FrameworkException
     */
    public static function get($capabilityName, Request $request)
    {
        if (!self::exists($capabilityName)) {
            throw new FrameworkException("No capability $capabilityName found in index. Is the metadata up to date?");
        }

        $class = self::$index[$capabilityName];
        if (is_string($class)) {
            if (!class_exists($class)) {
                throw new FrameworkException("Unknown class $class associated with capability $capabilityName");
            }
            $class = new $class;
            if ($class instanceof Service) {
                DependencyInjector::inject($class, $request);
            }
            self::$index[$capabilityName] = $class;
        }

        return $class;
    }

    public static function exists($capabilityName)
    {
        if (self::$index === null) {
            self::$index = CapabilitiesIndexGenerator::loadIndex();
        }

        return isset(self::$index[$capabilityName]);
    }
}
