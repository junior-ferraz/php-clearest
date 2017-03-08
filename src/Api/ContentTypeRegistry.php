<?php
namespace CleaRest\Api;


use CleaRest\Api\Encoders\FormUrlEncoder;
use CleaRest\Api\Encoders\JsonEncoder;
use CleaRest\Api\Encoders\MultipartFormDataEncoder;

/**
 * Registry for ContentTypeEncodes
 */
class ContentTypeRegistry
{
    private static $registry = [];

    private static $defaultEncoder;

    /**
     * Register default content type encoders
     * a JsonEncoder will be set as default when content type is not specified
     */
    public static function registerDefault() {
        // by default JSON is taken
        $jsonEncoder = new JsonEncoder();
        self::setDefaultEncoder($jsonEncoder);
        self::register($jsonEncoder);
        self::register(new FormUrlEncoder());
        self::register(new MultipartFormDataEncoder());
    }

    /**
     * Register given $encoder for content type handling
     * @param ContentTypeEncoder $encoder
     */
    public static function register(ContentTypeEncoder $encoder) {
        self::$registry[strtolower($encoder->getContentType())] = $encoder;
    }

    /**
     * Returns encoder for given content type.
     * If content type is null, returns default encoder
     * @param string $contentType
     * @return ContentTypeEncoder
     */
    public static function getEncoder($contentType = null) {
        if ($contentType === null) {
            return self::$defaultEncoder;
        }
        $contentType = strtolower($contentType);
        foreach (self::$registry as $type => $encoder) {
            if (stripos($contentType, $type) !== false) {
                return $encoder;
            }
        }
        return null;
    }

    /**
     * Sets default content type when request has no Content-Type header
     * @param ContentTypeEncoder $encoder
     */
    public static function setDefaultEncoder(ContentTypeEncoder $encoder)
    {
        self::$defaultEncoder = $encoder;
    }
}
