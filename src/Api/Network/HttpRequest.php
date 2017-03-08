<?php
namespace CleaRest\Api\Network;


use CleaRest\Api\Request;
use CleaRest\Services\DependencyContainer;


class HttpRequest implements Request
{
    const METHOD_HEAD = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';

    private $dependencyContainer;

    private $body;

    private $headers;

    /**
     * @return string
     */
    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @return string
     */
    public function getScriptName()
    {
        return $_SERVER['SCRIPT_NAME'];
    }

    /**
     * @return DependencyContainer
     */
    public function getDependencyContainer()
    {
        if ($this->dependencyContainer === null) {
            $this->dependencyContainer = new DependencyContainer();
        }
        return $this->dependencyContainer;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasField($name)
    {
        return array_key_exists($name, $_REQUEST);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getField($name)
    {
        return $this->hasField($name) ? $_REQUEST[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setField($name, $value)
    {
        $_REQUEST[$name] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        if ($this->body === null) {
            $this->body = file_get_contents('php://input');
        }
        return $this->body;
    }

    /**
     * @param mixed $data
     */
    public function setBody($data)
    {
        $this->body = $data;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getHeader($name)
    {
        if ($this->headers === null) {
            $this->headers = [];
            foreach (getallheaders() as $k => $v) {
                $this->headers[strtoupper($k)] = $v;
            }
        }
        $name = strtoupper($name);
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }
}
