<?php
namespace CleaRest\Api;

use CleaRest\Services\DependencyContainer;

interface Request
{
    /**
     * @return DependencyContainer
     */
    public function getDependencyContainer();

    /**
     * @return string
     */
    public function getMethod();
    /**
     * @return string
     */
    public function getUri();

    /**
     * @return string
     */
    public function getScriptName();

    /**
     * @param string $name
     * @return bool
     */
    public function hasField($name);

    /**
     * @param string $name
     * @return mixed
     */
    public function getField($name);

    /**
     * @param string $name
     * @param mixed $value
     * @return Request
     */
    public function setField($name, $value);

    /**
     * @return mixed
     */
    public function getBody();

    /**
     * @param mixed $data
     */
    public function setBody($data);

    /**
     * @param string $name
     * @return string
     */
    public function getHeader($name);
}
