<?php namespace Sframe\View\Plugins;
use Sframe\View\Compiler;

abstract class PluginAbstract
{
    /**
     * @var Compiler
     */
    protected $_compiler;

    public function __construct(Compiler $compiler)
    {
        $this->_compiler = $compiler;
    }
}