<?php

namespace CleaRest\Services;

use CleaRest\FrameworkException;

/**
 * Keeps the index of dependencies. It's basically a list of objects indexed by their class name.
 * That means the container considers the object type as a *singletone*, not allowing two instances of the same direct class.
 */
class DependencyContainer
{
    /**
     * List of previous set Dependencies
     *
     * @var Service[]
     */
    private $dependencies = array();

    /**
     * Set a Dependency to the container.
     * If this Dependency is already set throws an exception
     * To replace the Dependency set the parameter $replace to true
     *
     * @param Service $service
     * @param bool $replace = false
     * @throws FrameworkException
     */
    public function set(Service $service, $replace = false)
    {
        $className = get_class($service);
        if ($this->has($className) && !$replace) {
            throw new FrameworkException("Dependency $className is already in container");
        }
        $this->dependencies[$className] = $service;
    }

    /**
     * Returns a Dependency or null when it is not yet set
     * @param string $className
     * @return Service
     */
    public function get($className)
    {
        return $this->dependencies[$className];
    }

    /**
     * Checks whether the Dependency is already set
     *
     * @param string $className
     * @return bool
     */
    public function has($className)
    {
        return isset($this->dependencies[$className]);
    }

    /**
     * Removes the Dependency from the container
     * @param string $className
     * @return Service
     */
    public function remove($className)
    {
        $dependency = $this->dependencies[$className];
        unset($this->dependencies[$className]);
        return $dependency;
    }
}
