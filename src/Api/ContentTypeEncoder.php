<?php
namespace CleaRest\Api;

/**
 * Implement this class and register its instance in the ContentTypeRegistry to be handled automatically
 */
interface ContentTypeEncoder
{
    /**
     * Encodes data into expected content type
     * @param mixed $data
     * @return mixed
     */
    public function encode($data);

    /**
     * Decodes content from expected type into array representation
     * @param mixed $data
     * @return mixed
     */
    public function decode($data);

    /**
     * Returns content type header value
     * @return string
     */
    public function getContentType();
}
