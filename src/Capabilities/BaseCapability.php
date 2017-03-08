<?php
namespace CleaRest\Capabilities;


use CleaRest\Metadata\Annotations\Alias;
use CleaRest\Metadata\MetadataStorage;
use CleaRest\Services\BaseService;

/**
 * Base class used to create new capabilities.
 */
abstract class BaseCapability extends BaseService implements Capability
{
    public function getErrorMessage()
    {
        $metadata = MetadataStorage::getClassMetadata(get_class($this));
        foreach ($metadata->annotations as $annotation) {
            if ($annotation instanceof Alias) {
                return "Forbidden. User has no capability {$annotation->name}";
            }
        }
        return "Forbidden";
    }

    public function getErrorCode()
    {
        return CapabilityException::FORBIDDEN;
    }
}
