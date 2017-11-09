<?php

namespace CleaRest\Api;

use CleaRest\FrameworkException;
use CleaRest\Metadata\MetadataStorage;
use CleaRest\Services\Service;
use Symfony\Component\Yaml\Yaml;

/**
 * Helper class to create a router from file
 */
class RouterBuilder
{
    /**
     * Creates Router based on $yaml File. Expected structure is as following:
     * Handlers:
     *     AddDefault: true|false
     *     Classes:
     *         - Full\Qualified\RequestHandler\Class
     * Routes:
     *     my/route/with/$parameter:
     *         GET: Full\Qualified\Service\Interface::methodName
     *         POST: Full\Qualified\Service\Interface::methodName
     *         PUT: # open the node for specific implementation version
     *             Service: Full\Qualified\Service\Interface
     *             Method: methodName
     *             Version: VersionNumber|VersionName
     *
     * @param string $yamlFile
     * @return Router
     * @throws FrameworkException
     */
    public static function createFromFile($yamlFile)
    {
        $yaml = Yaml::parse(file_get_contents($yamlFile));

        $registerDefaultExceptionHandlers = true;
        if (isset($yaml['Handlers']['AddDefault'])) {
            $registerDefaultExceptionHandlers = $yaml['Handlers']['AddDefault'];
        }
        $router = new Router($registerDefaultExceptionHandlers);

        if (isset($yaml['Handlers']['Classes'])) {
            foreach ($yaml['Handlers']['Classes'] as $class) {
                if (!class_exists($class) || !is_subclass_of($class, RequestHandler::class)) {
                    throw new FrameworkException("Invalid ExceptionHandler $class in config file");
                }
                $router->addHandler(new $class);
            }
        }

        foreach ($yaml['Routes'] as $route => $methods) {
            foreach ($methods as $method => $entry) {
                if (is_string($entry)) {
                    list($serviceName, $serviceMethod) = explode(':', $entry);
                    $version = null;
                } else {
                    $serviceName = $entry['Service'];
                    $serviceMethod = $entry['Method'];
                    $version = isset($entry['Version']) ? $entry['Version'] : null;
                }
                $metadata = MetadataStorage::getClassMetadata($serviceName);
                /*if (!interface_exists($serviceName)) {
                    throw new FrameworkException(
                        "Cannot set $serviceName for route $method $route: " .
                        "Interface not found"
                    );
                }*/
                if (!in_array(Service::class, $metadata->implements)) {
                    throw  new FrameworkException(
                        "Cannot set $serviceName for route $method $route: " .
                        "$serviceName is not a service"
                    );
                }
                if (!array_key_exists($serviceMethod, $metadata->methods)) {
                    throw New FrameworkException("Unknown method $serviceName:$serviceMethod for $method $route");
                }
                $router->getRoute($route)->addMethod($method, $serviceName, $serviceMethod, $version);
            }
        }

        return $router;
    }

}

