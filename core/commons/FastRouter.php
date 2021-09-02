<?php
namespace Albin\core\commons;

use Albin\core\config\Config;
use FastRoute;

class FastRouter {

    private function __construct()
    {
    }

    public static function init()
    {

        $controllerPaths = Config::get('router.controllers');
        foreach ($controllerPaths as $controllerPath) {
            $files = glob(APP_DIR . "/" . $controllerPath . "*.php");

            if ($files) foreach ($files as $file) {
                require $file;
            }
        }

        var_dump(

            get_declared_classes()
        );


        // 路由
//        $dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
//
//        });

    }

}