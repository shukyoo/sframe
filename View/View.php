<?php namespace Sframe\View;

use Sframe\Router;

/**
 * 开发模式：每次运行都是重新编译，编译后的文件存在于版本库里
 * 生产模式：不检查，直接使用编译后的文件（除非编译文件不存在）
 * . 编译：多碎片合成
 * . 多语言模式下的语言变量替换，需要编译保存为不同语言下的模板文件
 * . 资源的管理（这个是另外的功能）
 * . 自定义html宏（这个是html部分另外的功能）
 *
 * strtr($message, $replace);
 * test{hello}aaa{vvv}aaa     ['{hello}' => 'aaaaa', '{vvv}' => 'bbbbb']
 */
class View
{
    protected $_view_path = '';

    /**
     * @var Router
     */
    protected $_router;

    // If true, it will always recompile，recommend "true" for development, "false" for production.
    protected $_recompile = false;

    protected $_locale;

    public function __construct($view_path, Router $router, $options = [])
    {
        $this->_view_path = rtrim($view_path, '/');
        $this->_router = $router;

        if (isset($options['recompile'])) {
            $this->_recompile = (bool)$options['recompile'];
        }

        if (isset($options['locale'])) {
            $this->_locale = strtolower($options['locale']);
        }
    }

    /**
     * @return string
     */
    public function getViewPath()
    {
        return $this->_view_path;
    }

    /**
     * @return string
     */
    public function getCompiledPath()
    {
        $loc = $this->_locale ? "/{$this->_locale}" : '';
        return "{$this->_view_path}/_compiled{$loc}";
    }

    /**
     * get router
     */
    public function getRouter()
    {
        return $this->_router;
    }

    /**
     * @param string $template
     * @return string
     */
    public function template($template)
    {
        $template = ltrim($template, '/');
        if (!strpos($template, '.')) {
            $template .= '.php';
        }
        $compiled_file = $this->getCompiledPath() .'/'. $template;

        if (!is_file($compiled_file) || $this->_recompile) {
            $compiler = new Compiler($this);
            $compiler->compile($template);
        }

        return $compiled_file;
    }
}

