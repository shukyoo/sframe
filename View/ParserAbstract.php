<?php namespace Sframe\View;

abstract class ParserAbstract
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