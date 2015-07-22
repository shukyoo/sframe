<?php namespace Sframe;

abstract class Controller
{
    public $router = null;

    public function __construct(\Sframe\Router $router)
    {
        $this->router = $router;
        $this->init();
    }

    public function init()
    {
    }

    public function route($path_arr)
    {
    }

    public function redirect($uri, $app = null)
    {
        $this->router->to($uri, $app);
    }

    public function render()
    {

    }
}