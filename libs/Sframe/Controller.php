<?php namespace Sframe;

abstract class Controller
{
    public $router = null;

    public function __construct(\Sframe\Router $router)
    {
        $this->router = $router;
    }

    abstract public function act();
}