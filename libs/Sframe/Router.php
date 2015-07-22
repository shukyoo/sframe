<?php namespace Sframe;

class Router
{
    public $app_path = '';
    
    public $uri = '';
    public $uri_base = '';
    public $uri_script = '';
    public $uri_path = '';
    public $uri_query = '';

    public $controller = 'home';

    protected static $_app_domains = [];

    public static function config($app_domains)
    {
        self::$_app_domains = $app_domains;
    }

    public function __construct($app_path)
    {
        $this->app_path = rtrim($app_path, '/');

        $this->uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $this->uri_script = filter_has_var(INPUT_SERVER, 'SCRIPT_NAME') ? filter_input(INPUT_SERVER, 'SCRIPT_NAME') : filter_input(INPUT_SERVER, 'PHP_SELF');
        $this->uri_base = rtrim(dirname($this->uri_script), '\\/');

        $this->uri_path = $this->uri;
        if (($poz = strpos($this->uri, '?')) !== false) {
            $this->uri_query = substr($this->uri, $poz + 1);
            $this->uri_path = substr($this->uri, 0, $poz);
        }

        if ($this->uri_script && strpos($this->uri_path, $this->uri_script) === 0) {
            $this->uri_path = substr($this->uri_path, strlen($this->uri_script));
        } elseif ($this->uri_base && strpos($this->uri_path, $this->uri_base) === 0) {
            $this->uri_path = substr($this->uri_path, strlen($this->uri_base));
        }

        $this->uri_path = preg_replace('/\/+/', '/', trim($this->uri_path, '/'));
    }


    /**
     * Dispatch route
     * @throws \Exception
     */
    public function dispatch()
    {
        $uri_path_arr = explode('/', strtolower($this->uri_path));
        empty($uri_path_arr[0]) || $this->controller = $uri_path_arr[0];
        $controller = ucfirst($this->controller) . 'Controller';
        $controller_file = "{$this->app_path}/controllers/{$controller}.php";
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
            $route = $class->route($uri_path_arr);
            if (empty($route)) {
                $action = $uri_path_arr[1];
            } else {
                if (is_string($route)) {
                    $action = $route;
                } else {
                    $action = $route[0];
                    empty($route[1]) || $args = $route[1];
                }
            }
        }
        $action = 'act'. ucfirst($action);
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
    public function route($uri, $app = null)
    {
        $uri = trim($uri, '/');
        $domain = ($app && !empty(self::$_app_domains[$app])) ? rtrim(self::$_app_domains[$app], '/') : '';
        return "{$domain}{$this->uri_base}/{$uri}";
    }

    /**
     * Route to
     */
    public function to($uri, $app = null)
    {
        header("Location: {$this->route($uri, $app)}");
        exit;
    }

}