<?php namespace Sframe;

abstract class Controller
{
    /**
     * @var Router
     */
    protected $_router;

    public function __construct(Router $router)
    {
        $this->_router = $router;
        $this->_init();
    }

    protected function _init()
    {
    }

    public function setRoute($path_arr, &$args = [])
    {
    }

    public function route($uri, $params = null)
    {
        return $this->_router->route($uri, $params);
    }

    public function redirect($uri, $params = null)
    {
        $this->_router->redirect($uri, $params);
    }
}