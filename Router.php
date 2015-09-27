<?php namespace Sframe;

class Router
{
    public $app_path = '';
    public $controller = 'home';


    public static function uri()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_URI');
    }

    public static function script()
    {
        return filter_has_var(INPUT_SERVER, 'SCRIPT_NAME') ? filter_input(INPUT_SERVER, 'SCRIPT_NAME') : filter_input(INPUT_SERVER, 'PHP_SELF');
    }

    public static function base()
    {
        return rtrim(dirname(self::script()), '\\/');
    }

    public static function path()
    {
        $script = self::script();
        $base = self::base();
        $uri_path = self::uri();
        if (($poz = strpos($uri_path, '?')) !== false) {
            $uri_path = substr($uri_path, 0, $poz);
        }
        if ($script && strpos($uri_path, $script) === 0) {
            $uri_path = substr($uri_path, strlen($script));
        } elseif ($base && strpos($uri_path, $base) === 0) {
            $uri_path = substr($uri_path, strlen($base));
        }
        return preg_replace('/\/+/', '/', trim($uri_path, '/'));
    }


    public function __construct($app_path)
    {
        $this->app_path = rtrim($app_path, '/');
    }

    /**
     * Set default controller
     */
    public function setDefaultController($controller)
    {
        $this->controller = strtolower(trim($controller));
        return $this;
    }


    /**
     * Dispatch route
     * @throws \Exception
     */
    public function dispatch()
    {
        $path = self::path();
        $uri_path_arr = explode('/', strtolower($path));
        empty($uri_path_arr[0]) || $this->controller = $uri_path_arr[0];
        $controller = ucfirst($this->controller) . 'Controller';
        $controller_file = "{$this->app_path}/controller/{$controller}.php";
        if (!is_file($controller_file)) {
            throw new \Exception("Invalid controller {$controller}");
        }
        require $controller_file;
        $class = new $controller($this);
        if (!$class instanceof Controller) {
            throw new \Exception("{$controller} must extends \\Sframe\\Controller");
        }

        // Call action
        $action = 'index';
        $args = [];
        if (!empty($uri_path_arr[1])) {
            $route = $class->route($uri_path_arr, $args);
            $action = $route ?: $uri_path_arr[1];
        }
        $action = str_replace('-', '', ucwords($action, '-'));
        $action = "act{$action}";
        if (!method_exists($class, $action)) {
            throw new \Exception("{$action} not exists in {$controller}");
        }
        if (empty($args)) {
            $class->$action();
        } else {
            call_user_func_array([$class, $action], $args);
        }
    }

    /**
     * Get route of uri
     */
    public static function route($uri)
    {
        return self::base() .'/'. trim($uri, '/');
    }

}