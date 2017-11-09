<?php
namespace CleaRest\Services;

use CleaRest\Api\Request;

/**
 * Classes marked as Services will be collected to the Services index
 * and their implementations will be checked with different versions.
 */
interface Service
{
	public function __construct();

    /**
     * This function is called after dependencies are set
     */
	public function afterInitialize();
    /**
     * @param string $name
     * @param Service $instance
     */
	public function setDependency($name, Service $instance);

    /**
     * @param Request $request
     */
	public function setRequest(Request $request);

    /**
     * @return Request
     */
	public function getRequest();
}


