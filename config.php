<?php
define('DEBUG', 1);

\Sutil\Database\DB::config(array(
    'driver' => 'mysql',
    'dbname' => 'test',
    'username' => 'root'
));

\Sframe\Router::config(array(
    'www' => 'http://www.hello.lc',
    'admin' => 'http://admin.hello.lc'
));