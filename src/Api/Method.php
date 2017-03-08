<?php
namespace CleaRest\Api;

/**
 * Route Method
 */
class Method
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $interfaceName;

    /**
     * @var string
     */
    private $interfaceMethod;

    /**
     * @var int|string
     */
    private $implementationVersion;

    /**
     * @param string $name
     * @param string $interfaceName
     * @param string $interfaceMethod
     * @param int|string $implementationVersion
     */
    public function __construct($name, $interfaceName, $interfaceMethod, $implementationVersion = null)
    {
        $this->name = $name;
        $this->interfaceName = $interfaceName;
        $this->interfaceMethod = $interfaceMethod;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getInterfaceName()
    {
        return $this->interfaceName;
    }

    /**
     * @return string
     */
    public function getInterfaceMethod()
    {
        return $this->interfaceMethod;
    }

    /**
     * @return int|string
     */
    public function getImplementationVersion()
    {
        return $this->implementationVersion;
    }
}
