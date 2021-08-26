<?php
namespace Albin\core\servers;

use Albin\core\config\Config;
use Albin\core\messages\CoroutineMessage;
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

        // 开启http 服务
          run(function () {

              Coroutine::create(function () {

                  $server = new Server(Config::get('server.http.host'), Config::get('server.http.port'), false);
                  $server->handle('/', function (Request $request, Response $response) {
                      CoroutineMessage::push(['rand' => rand(1000, 9999), 'index' => $request->fd]);
                      $response->end("<h1>Index</h1>");
                  });

                  $server->start();
              });

              // 开启websocket 服务
              Coroutine::create(function () {

                  $server = new Server(Config::get('server.websocket.host'), Config::get('server.websocket.port'), false);
                  $server->handle('/', function (Request $request, Response $ws) {
                      $ws->end("<h1>Index</h1>");
                  });

                  $server->start();
              });


              // 管道消息消费
              Coroutine::create(function () {
                  while (1) {
                      $data = CoroutineMessage::pop();
                      if ($data) {

                      } else {

                          echo "sleeping ... \r\n";
                          Coroutine::sleep(2);
                      }

                  }

              });

          });

    }








}