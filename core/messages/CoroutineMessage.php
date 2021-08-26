<?php
namespace Albin\core\messages;

use Albin\core\servers\SwooleChannel;
use Albin\exceptions\SystemException;
use Swoole\Http\Response;

class CoroutineMessage extends SwooleChannel
{

    private static $clientSession = [];

    public static function bindHttpToken($token, Response $ws)
    {

        if (!isset(self::$clientSession[$token])) {

            self::$clientSession[$token] = $ws;
        }

        return self::$clientSession;
    }

    public static function sendMsg($token, $body)
    {
        if (isset(self::$clientSession[$token])) {

            self::push(['ws' => self::$clientSession[$token], 'body' => $body]);
        }

        throw new SystemException('please bind token client session');

    }


    public static function resumeMsg($body)
    {
        $body['ws']->push($body['body']);

    }


}