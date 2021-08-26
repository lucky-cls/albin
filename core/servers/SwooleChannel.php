<?php
namespace Albin\core\servers;

use Albin\core\config\Config;
use Albin\core\messages\CoroutineMessage;
use Albin\exceptions\SystemException;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

abstract class SwooleChannel
{
    protected static $singleChannel = null;

    private function __construct()
    {
    }

    private static function init()
    {

        if (static::$singleChannel === null) {

            static::$singleChannel = new Channel(Config::get('server.channel.size'));
        }

        return static::$singleChannel;
    }



    abstract static function resumeMsg($body);


    public static function resume()
    {
        Coroutine::create(function () {

            $data = CoroutineMessage::pop();
            if ($data) {

                static::resumeMsg($data);

            } else {

                echo "sleeping ... \r\n";
                Coroutine::sleep(2);
            }

        });
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