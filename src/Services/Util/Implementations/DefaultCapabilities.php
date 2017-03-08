<?php
namespace CleaRest\Services\Util\Implementations;

use CleaRest\Capabilities\CapabilitiesFactory;
use CleaRest\Capabilities\CapabilityException;
use CleaRest\Services\BaseService;
use CleaRest\Services\Util\Capabilities;

/**
 * @version 1
 */
class DefaultCapabilities extends BaseService implements Capabilities
{

    /**
     * Returns the status of a capability for current request
     *
     * @param string $capability name
     * @return bool
     */
    public function check($capability)
    {
        $instance = CapabilitiesFactory::get($capability, $this->getRequest());
        return $instance->check();
    }

    /**
     * Checks if capability is allowed for current request.
     * If not, throws a CapabilityException
     *
     * @param string $capability name
     * @throws CapabilityException
     */
    public function assert($capability)
    {
        $instance = CapabilitiesFactory::get($capability, $this->getRequest());
        if (!$instance->check()) {
            throw CapabilityException::fromCapability($instance);
        }
    }
}