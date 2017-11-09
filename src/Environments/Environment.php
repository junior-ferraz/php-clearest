<?php
namespace CleaRest\Environments;

abstract class Environment
{
    /**
     * @var Environment
     */
    private static $current;

    public static function setCurrent(Environment $environment)
    {
        self::$current = $environment;
    }

    /**
     * Returns current environment instance
     * @return Environment
     */
    public static function getCurrent()
    {
        if (self::$current === null) {
            self::setCurrent(new DevEnvironment());
        }
        return self::$current;
    }

    /**
     * Returns true if current enviorment is of called class.
     * This function should be called only in the child class. Examples:
     *    - DevEnvironment::isCurrent()
     *    - LiveEnvironment::isCurrent()
     * @return bool
     */
    public static function isCurrent()
    {
        if (self::getCurrent() instanceof static) {
            return true;
        }
    }
}
