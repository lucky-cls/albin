<?php
namespace Albin\core\servers;

use Albin\core\config\Config;
use Albin\core\messages\CoroutineMessage;
use Swoole\Coroutine;
use Swoole\Http\Request;
use Swoole\Http\Response;
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
                      $response->end("<h1>Index</h1>");
                  });

                  $server->start();
              });

              // 开启websocket 服务
              Coroutine::create(function () {

                  $server = new Server(Config::get('server.websocket.host'), Config::get('server.websocket.port'), false);
                  $server->handle('/websocket', function (Request $request, Response $ws) {
                      $ws->upgrade();
                      while (true) {
                          $frame = $ws->recv();
                          if ($frame === '') {
                              $ws->close();
                              break;
                          } else if ($frame === false) {
                              echo 'errorCode: ' . swoole_last_error() . "\n";
                              $ws->close();
                              break;
                          } else {
                              if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                                  $ws->close();
                                  break;
                              }
                              CoroutineMessage::push(['rand' => rand(1000, 9999), 'index' => $ws]);
                              $ws->push("Hello {$frame->data}!");
                              $ws->push("How are you, {$frame->data}?");
                          }
                      }
                  });

                  $server->handle('/', function (Request $request, Response $response) {
                      $response->end(<<<HTML
    <h1>Swoole WebSocket Server</h1>
    <script>
var wsServer = 'ws://127.0.0.1:9502/websocket';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
    console.log("Connected to WebSocket server.");
    websocket.send('hello');
};

websocket.onclose = function (evt) {
    console.log("Disconnected");
};

websocket.onmessage = function (evt) {
    console.log('Retrieved data from server: ' + evt.data);
};

websocket.onerror = function (evt, e) {
    console.log('Error occured: ' + evt.data);
};
</script>
HTML
                      );
                  });

                  $server->start();
              });



              // 消费
              CoroutineMessage::resume();

          });

    }








}