<?php
namespace Albin\core\servers;

use Albin\app\controllers\Api\IndexController;
use Albin\core\commons\FastRouter;
use Albin\core\config\Config;
use Albin\core\messages\CoroutineMessage;
use Swoole\Coroutine;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Coroutine\Http\Server;
use FastRoute;
use function Swoole\Coroutine\run;


class SwooleServer{

    private static $single = null;


    private function __construct()
    {
    }


    // 初始化
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
                  // 开启http 服务

                  $server = new Server(Config::get('server.http.host'), Config::get('server.http.port'), false);

                  // 路由
                  $dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
                      $r->addRoute('GET', '/test', function () {

                      });
                  });

                  FastRouter::init();


                  $server->handle('/', function (Request $request, Response $response) use ($dispatcher) {

//                      var_dump(
//
//                          Config::get('router')
//                      );
                      $httpMethod = $request->server['request_method'];
                      $uri = $request->server['request_uri'];
                      // Strip query string (?foo=bar) and decode URI
                      if (false !== $pos = strpos($uri, '?')) {
                          $uri = substr($uri, 0, $pos);
                      }
                      $uri = rawurldecode($uri);
                      $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
//                      var_dump($routeInfo);
                      switch ($routeInfo[0]) {
                          case FastRoute\Dispatcher::NOT_FOUND:
                              $response->status(404);
                              break;
                          case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                              $allowedMethods = $routeInfo[1];
                              $response->status(405);
                              break;
                          case FastRoute\Dispatcher::FOUND:
                              $handler = $routeInfo[1];
                              $vars = $routeInfo[2];
                              $handler();
                              // ... call $handler with $vars
                              break;
                      }


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
                              CoroutineMessage::push(['body' => rand(1000, 9999), 'ws' => $ws]);
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