<?php
namespace CleaRest\Api;


interface Response
{
    /**
     * @param int $code
     */
    public function setStatusCode($code);

    /**
     * @return int
     */
    public function getStatusCode();

    /**
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value);

    /**
     * @param string $name
     * @return string
     */
    public function getHeader($name);

    /**
     * @return string[]
     */
    public function getAllHeaders();

    /**
     * @param string $data
     */
    public function setBody($data);

    /**
     * @return string
     */
    public function getBody();

    public function render();
}
