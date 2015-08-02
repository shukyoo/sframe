<?php namespace Sframe;

abstract class Controller
{
    public $router = null;

    protected $_env = ENV_PROD;
    protected $_locale;

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

    /**
     * @return \Sutil\View\View
     */
    public function template($template)
    {
        return (new \Sutil\View\View("{$this->router->app_path}/views", array(
            'recompile' => ($this->_env == ENV_DEV),
            'locale' => $this->_locale
        )))->template($template);
    }
}