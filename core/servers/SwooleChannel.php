<?php
namespace Albin\core\servers;

use Albin\core\config\Config;
use Albin\exceptions\SystemException;
use Swoole\Coroutine\Channel;

class SwooleChannel
{
    private static $singleChannel = null;

    private function __construct()
    {
    }

    private static function init()
    {

        if (self::$singleChannel === null) {

            self::$singleChannel = new Channel(Config::get('server.channel.size'));
        }

        return self::$singleChannel;
    }



    public static function __callStatic($name, $arguments)
    {
        if (method_exists(self::init(), $name)
            && is_callable([self::init(), $name])
        ) {

            return call_user_func_array([self::init(), $name], $arguments);
        }


        throw new SystemException('config method not found or not be called ');
    }


}