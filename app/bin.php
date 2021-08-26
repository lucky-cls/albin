<?php

define('ROOT_DIR', dirname(__DIR__));
define('APP_DIR', ROOT_DIR . '/app');
require ROOT_DIR . '/vendor/autoload.php';


\Albin\core\servers\SwooleServer::init();





