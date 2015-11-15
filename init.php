<?php
// Environment
define('ENV_DEV', 'dev');
define('ENV_TEST', 'test');
define('ENV_PROD', 'prod');

define('D_TIMESTAMP', time());
define('D_DATETIME', date('Y-m-d H:i:s', D_TIMESTAMP));

require __DIR__ .'/ClassLoader.php';
\Sframe\ClassLoader::register();


function route($uri, $params = null)
{
    return Sframe\Router::route($uri, $params);
}

function redirect($uri)
{
    header('Location: '. route($uri));
    exit;
}

