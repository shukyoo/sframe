<?php
define('ROOT_PATH', __DIR__);
define('ST_ROOT', 'E:\www\sutil');
define('DS', DIRECTORY_SEPARATOR);
define('D_TIMESTAMP', time());
define('D_DATETIME', date('Y-m-d H:i:s', D_TIMESTAMP));


// Autoload
require_once ROOT_PATH .'/ClassLoader.php';
ClassLoader::register();
ClassLoader::addDirectories(array(
    'Sutil' => ST_ROOT,
    'Sframe' => ROOT_PATH .'/libs/Sframe'
));


require ROOT_PATH .'/config.php';
defined('DEBUG') || define('DEBUG', 0);



function p($var, $is_dump = false)
{
    echo '<pre>';
    if ($is_dump) {
        var_dump($var);
    } else {
        print_r($var);
    }
    exit;
}