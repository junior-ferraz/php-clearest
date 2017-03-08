<?php
namespace CleaRest\Api\Network;


use CleaRest\Api\ContentTypeRegistry;
use CleaRest\Api\Exceptions\RequestException;
use CleaRest\Api\Response;

class HttpResponse implements Response
{
    /**
     * @var int
     */
    private $code = 0;
    /**
     * @var string[]
     */
    private $headers = [];
    /**
     * @var string
     */
    private $body;

    /**
     * @param int $code
     */
    public function setStatusCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->code;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeader($name)
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        return null;
    }

    /**
     * @return string[]
     */
    public function getAllHeaders()
    {
        return $this->headers;
    }

    /**
     * @param mixed $data
     */
    public function setBody($data)
    {
        $this->body = $data;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    public function render()
    {
        header('HTTP/1.0 ' . $this->code);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        if (is_string($this->body)) {
            print $this->body;
        }
    }
}
