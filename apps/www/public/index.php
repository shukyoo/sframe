<?php
define('APP_PATH', dirname(__DIR__));
require APP_PATH .'/../../boot.php';

$router = new \Sframe\Router(APP_PATH);

try {

    $router->dispatch();

} catch (\Exception $e) {
    if (DEBUG) {
        echo $e->getMessage();
    } else {
        $router->to('404.html');
    }
}
