<?php
define('APP_PATH', dirname(__DIR__));
require APP_PATH .'/../../boot.php';
require APP_PATH .'/controllers/BaseController.php';

$router = new \Sframe\Router(APP_PATH);

try {

    $router->dispatch();

} catch (\Exception $e) {
    if (IN_DEV) {
        echo $e->getMessage();
    } else {
        $router->to('404.html');
    }
}
