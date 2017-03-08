<?php
namespace CleaRest\Services\Util\Implementations;

use CleaRest\Services\BaseService;
use CleaRest\Services\Util\Session;

/**
 * Default $_SESSION variable handling
 * @version 1
 */
class PhpSession extends BaseService implements Session
{

    public function afterInitialize()
    {
       parent::afterInitialize();
       $this->start();
    }

    public function start()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->has($key) ? $_SESSION[$key] : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function destroy()
    {
        if (session_status() === PHP_SESSION_ACTIVE){
            session_destroy();
        }
    }
}
