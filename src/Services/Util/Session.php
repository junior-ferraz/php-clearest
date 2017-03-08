<?php
namespace CleaRest\Services\Util;


use CleaRest\Services\Service;

/**
 * This service provides the session functionalities.
 * You can create your own version by implementing this interface and annotating with @version higher than 1.
 */
interface Session extends Service
{
    /**
     * Starts the session
     */
    public function start();

    /**
     * Checks if session has $key
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * Returns current value of $key
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Sets $value for $key
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value);

    /*
     * Destroys the session
     */
    public function destroy();
}