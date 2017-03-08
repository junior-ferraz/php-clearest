<?php
namespace CleaRest\Api\Exceptions;

/**
 * This exception does not extend ApiException because it should not be returned to the client
 */
class InternalException extends \Exception
{

}