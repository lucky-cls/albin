<?php
namespace Albin\core\servers;

use Albin\core\config\Config;
use Swoole\Coroutine;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Process;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

class SwooleServer{

    private static $single = null;

    public static function init()
    {

        if (self::$single === null) {
            self::$single = new self();
        }

        self::$single->startHttp();

    }

    private function startHttp()
    {
          run(function () {

              Coroutine::create(function () {

                  $server = new Server(Config::get('server.http.host'), Config::get('server.http.port'), false);
                  $server->handle('/', function (Request $request, Response $response) {
                      $response->end("<h1>Index</h1>");
                  });

                  $server->start();
              });

              Coroutine::create(function () {

                  $server = new Server(Config::get('server.websocket.host'), Config::get('server.websocket.port'), false);
                  $server->handle('/', function (Request $request, Response $response) {
                      $response->end("<h1>Index</h1>");
                  });

                  $server->start();
              });


          });


    }






}