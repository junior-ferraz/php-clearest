<?php

namespace CleaRest\Services;

use CleaRest\Api\Request;
use CleaRest\Capabilities\CapabilitiesFactory;
use CleaRest\Capabilities\CapabilityException;
use CleaRest\FrameworkException;
use CleaRest\Metadata\Annotations\Assert;
use CleaRest\Metadata\Descriptors\Clazz;
use CleaRest\Metadata\MetadataStorage;
use CleaRest\Validation\Context\MethodContext;
use CleaRest\Validation\ValidationException;
use CleaRest\Validation\Validator;

/**
 * Proxies are used to wrap services and validate their method arguments before it's called
 */
class ServiceProxy implements Service
{
    /**
     * @var Service
     */
    private $instance;
    /**
     * @var Clazz
     */
    private $metadata;

    public static function create(Service $service)
    {
        $proxy = new self();
        $proxy->instance = $service;
        $proxy->metadata = MetadataStorage::getClassMetadata(get_class($service));
        return $proxy;
    }

    public function __construct()
    {

    }

    public function afterInitialize()
    {
        $this->instance->afterInitialize();
    }

    /**
     * @param string $name
     * @param Service $instance
     */
    public function setDependency($name, Service $instance)
    {
        $this->instance->setDependency($name, $instance);
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->instance->setRequest($request);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->instance->getRequest();
    }

    /**
     * @return Service
     */
    public function getInstance()
    {
        return $this->instance;
    }

    public function __call($name, $arguments) {
        if (!isset($this->metadata->methods[$name])) {
            throw new FrameworkException("Service {$this->serviceClass} does not have metadata for method {$name}");
        }
        $metadata = $this->metadata->methods[$name];

        // Executing assertions
        foreach ($metadata->annotations as $annotation) {
            if ($annotation instanceof Assert) {
                $capability = CapabilitiesFactory::get($annotation->capability, $this->getRequest());
                if (!$capability->check()) {
                    throw CapabilityException::fromCapability($capability);
                }
            }
        }

        // Validating arguments
        $context = new MethodContext();
        $context->class = get_class($this->instance);
        $violations = Validator::validateArguments($arguments, $metadata, $context);
        if (!empty($violations)) {
            throw new ValidationException($violations, $context);
        }

        try {
            return call_user_func_array(
                array($this->instance, $name),
                $arguments
            );
        } catch (ValidationException $exception) {
            // Validation exceptions in internal calls should not be returned to the client
            $exception->setApiVisible(false);
            throw $exception;
        }
    }

    public function __set($name, $value) {
        $this->instance->$name = $value;
    }

    public function __get($name) {
        return $this->instance->$name;
    }

    public function __unset($name) {
        unset($this->instance->$name);
    }

    public function __isset($name) {
        return isset($this->instance->$name);
    }

}