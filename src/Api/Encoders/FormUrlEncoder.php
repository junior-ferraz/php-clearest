<?php
namespace CleaRest\Api\Encoders;

use CleaRest\Api\ContentTypeEncoder;

/**
 * Encodes and decodes x-www-form-urlencoded content-type
 */
class FormUrlEncoder implements ContentTypeEncoder
{

    /**
     * Returns content type name
     * @return string
     */
    public function getContentType()
    {
        return 'application/x-www-form-urlencoded';
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function encode($data)
    {
        return http_build_query($data);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function decode($data)
    {
        parse_str($data, $result);
        return $result;
    }

}