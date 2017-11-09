<?php
namespace CleaRest\Api\Encoders;

use CleaRest\Api\ContentTypeEncoder;

/**
 * Encodes and decodes JSON content-type
 */
class JsonEncoder implements ContentTypeEncoder
{

    /**
     * Returns content type name
     * @return string
     */
    public function getContentType()
    {
        return 'application/json';
    }

    /**
     * Encodes data into json
     * @param mixed $data
     * @return mixed
     */
    public function encode($data)
    {
        return json_encode($data);
    }

    /**
     * Decodes json into array structure
     * @param mixed $data
     * @return mixed
     */
    public function decode($data)
    {
        return json_decode($data, true);
    }

}