<?php namespace Sframe;

class Router
{
    /**
     * Dispatch route
     * @throws \Exception
     */
    public static function dispatch($base_path)
    {
        $path = Request::path();
        $path_arr = explode('/', strtolower($path));
        $controller = empty($path_arr[0]) ? 'index' : $path_arr[0];
        $controller = ucfirst($controller) .'Controller';
        $controller_file = rtrim($base_path, '/') .'/'. $controller .'.php';
        if (!is_file($controller_file)) {
            throw new \Exception("Invalid controller {$controller}");
        }
        require $controller_file;
        $controller_class = new $controller();

        // Call action
        $action = 'index';
        $args = [];
        if (!empty($path_arr[1])) {
            $route = $controller_class->setRoute($path_arr, $args);
            $action = $route ?: $path_arr[1];
        }
        $action = str_replace('-', '', ucwords($action, '-'));

        // GET or POST
        $action = (Request::isPost() ? 'post' : 'get') . $action;
        if (!method_exists($controller_class, $action)) {
            throw new \Exception("{$action} not exists in {$controller}");
        }
        if (empty($args)) {
            $controller_class->$action();
        } else {
            call_user_func_array([$controller_class, $action], $args);
        }
    }

    /**
     * Get route of uri
     */
    public static function route($uri, $params = null)
    {
        $param_str = '';
        if (null !== $params) {
            $param_str = '?'. (is_array($params) ? http_build_query($params) : $params);
        }
        return Request::base() .'/'. trim($uri, '/') . $param_str;
    }

}