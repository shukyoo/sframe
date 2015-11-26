<?php namespace Sframe;

class Router
{
    protected $_controller = 'index';
    protected $_action = 'index';
    protected $_base_path = '';
    protected $_url_base = '';
    protected $_url_path = '';

    public static function dispatch($base_path)
    {
        return new self($base_path);
    }

    public function __construct($base_path)
    {
        $this->_base_path = rtrim($base_path, '/');
        $this->_url_base = Request::base();
        $this->_url_path = Request::path();

        $path_arr = explode('/', strtolower($this->_url_path));
        if (!empty($path_arr[0])) {
            $this->_controller = $path_arr[0];
        }

        // foo-bar -> FooBarController
        $controller_name = str_replace('-', '', ucwords($this->_controller, '-')) .'Controller';

        // new controller instance
        $controller_file = $this->_base_path .'/'. $controller_name .'.php';
        if (!is_file($controller_file)) {
            throw new \Exception("Invalid controller {$controller_name}");
        }
        require $controller_file;
        $controller = new $controller_name($this);

        // find action
        $args = [];
        if (!empty($path_arr[1])) {
            $route = $controller->setRoute($path_arr, $args);
            $this->_action = $route ?: $path_arr[1];
        }
        $action = str_replace('-', '', ucwords($this->_action, '-'));

        // call action
        // GET or POST or Both
        $action = (Request::isPost() ? 'post' : 'get') . $action;
        if (!method_exists($controller, $action)) {
            throw new \Exception("{$action} not exists in {$controller_name}");
        }
        if (empty($args)) {
            $controller->$action();
        } else {
            call_user_func_array([$controller, $action], $args);
        }
    }

    public function controller()
    {
        return $this->_controller;
    }

    public function action()
    {
        return $this->_action;
    }

    public function route($uri, $params = null)
    {
        $param_str = '';
        if (null !== $params) {
            $param_str = '?'. (is_array($params) ? http_build_query($params) : $params);
        }
        return $this->_url_base .'/'. trim($uri, '/') . $param_str;
    }

    public function redirect($uri, $params = null)
    {
        header('Location: '. $this->route($uri, $params));
        exit;
    }
}