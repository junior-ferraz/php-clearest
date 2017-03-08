<?php
namespace CleaRest\Api;

class Route
{
    /**
     * @var string
     */
    private $route;

    /**
     * @var string
     */
    private $regexPattern;

    /**
     * @var string[]
     */
    private $parameters = [];

    /**
     * @var Method[]
     */
    private $methods = [];

    public function __construct($route)
    {
        $this->route = $route;
        if (preg_match_all('/\$[a-zA-Z_0-9]*/', $route, $matches)) {
            foreach ($matches[0] as $parameter) {
                $this->parameters[] = substr($parameter, 1);
                $route = str_replace($parameter, '([^/]*)', $route);
            }
        }
        $this->regexPattern = '/^\/?' . str_replace('/', '\\/', $route) . '\/?$/';
    }

    /**
     * Returns false if given url does not match to current route.
     * Returns an array with matched url parameters in case route matches pattern.
     * @param $route
     * @return bool|mixed[]
     */
    public function match($route)
    {
        if(!preg_match($this->regexPattern, $route, $matches))
        {
            return false;
        }
        $parameters = [];
        foreach ($this->parameters as $index => $name) {
            $parameters[$name] = $matches[$index+1];
        }
        return $parameters;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param string $name
     * @return Method
     */
    public function getMethod($name)
    {
        return isset($this->methods[$name]) ? $this->methods[$name] : null;
    }

    /**
     * @return Method[]
     */
    public function getAllMethods()
    {
        return $this->methods;
    }

    /**
     * @param string $requestMethod
     * @param string $interfaceName
     * @param string $methodName
     * @param string|int|null $version
     * @return $this
     */
    public function addMethod($requestMethod, $interfaceName, $methodName, $version = null)
    {
        $this->methods[$requestMethod] = new Method($requestMethod, $interfaceName, $methodName, $version);
        return $this;
    }
}
