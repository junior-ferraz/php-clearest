<?php
namespace CleaRest\Api\Encoders;

use CleaRest\Api\ContentTypeEncoder;

/**
 * Encodes and decodes multipart/form-data content-type
 */
class MultipartFormDataEncoder implements ContentTypeEncoder
{

    /**
     * Returns content type name
     * @return string
     */
    public function getContentType()
    {
        return 'multipart/form-data';
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function encode($data)
    {
        if (is_array($data)) {
            return http_build_query($data);
        } else {
            return $data;
        }
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function decode($data)
    {
        return $data;
    }

}