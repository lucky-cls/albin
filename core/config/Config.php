<?php
namespace Albin\core\config;

use Albin\exceptions\SystemException;
use Noodlehaus\Config as configPkg;

class Config {

    const CONFIG_DIR = APP_DIR . '/config';

    private static $singleConfig = null;

    private static function init()
    {
        if (self::$singleConfig === null) {

            self::$singleConfig = new configPkg(self::CONFIG_DIR);
        }

        return self::$singleConfig;
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists(self::init(), $name)
            && is_callable([self::init(), $name])
        ) {

            return call_user_func_array([self::init(), $name],
                $arguments);
        }

        throw new SystemException('config method not found or not be called ');
    }


}