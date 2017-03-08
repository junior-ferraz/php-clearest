<?php
namespace CleaRest\Services\Util;

use CleaRest\Capabilities\CapabilityException;
use CleaRest\Services\Service;

/**
 * This service allows you to check or assert capabilities
 */
interface Capabilities extends Service
{
    /**
     * Returns the status of a capability for current request
     * @param string $capability name
     * @return bool
     */
    public function check($capability);

    /**
     * Checks if capability is allowed for current request.
     * If not, throws a CapabilityException
     * @param string $capability name
     * @throws CapabilityException
     */
    public function assert($capability);
}
