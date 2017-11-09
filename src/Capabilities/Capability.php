<?php
namespace CleaRest\Capabilities;

use CleaRest\Services\Service;

/*
 * Implement this interface. It's not necessary to register a capability. The metadata generator will do it for you ;)
 */
interface Capability extends Service
{
    /**
     * Returns true when current request has current capability
     * @return bool
     */
    public function check();

    /**
     * Returns the error message that should be displayed for the user
     * @return string
     */
    public function getErrorMessage();

    /**
     * Return one of the constants in CapabilityException (UNAUTHORIZED, PAYMENT_REQUIRED or FORBIDDEN).
     * This code will be later mapped to respective http status code.
     * If not implemented this function returns by default the value is FORBIDDEN (response status 403)
     * @return int
     */
    public function getErrorCode();
}
