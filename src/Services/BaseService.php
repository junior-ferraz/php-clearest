<?php
namespace CleaRest\Services;

use CleaRest\Api\Request;

/**
 * Base class for services. It implement basic methods
 */
abstract class BaseService implements Service
{
    /**
     * @var Request
     */
    private $request;

    public final function __construct()
    {

    }

    public function afterInitialize()
    {

    }

    /**
     * @param string $name
     * @param Service $instance
     */
    public function setDependency($name, Service $instance)
    {
        $this->{$name} = $instance;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
