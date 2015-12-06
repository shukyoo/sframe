<?php namespace Sframe\View;

class Compiler
{
    protected $_view_path = '';
    protected $_compiled_path = '';
    protected static $_rules = array(
        '#\{\$([a-zA-Z_].+?)\}#' => ['\Sframe\View\Plugins\Basic', 'var'],
        '#\{\/\/\s+(.+?)\}#' => ['\Sframe\View\Plugins\Basic', 'comment'],
        '#\{include\s+(.+?)\}#' => ['\Sframe\View\Plugins\Basic', 'include'],
        '#\{trans\s+(.+?)\}#' => ['\Sframe\View\Plugins\Basic', 'trans'],
        '#\{escape\s+(.+?)\}#' => ['\Sframe\View\Plugins\Basic', 'escape'],
        '#\{if\s+(.+?)\}#' => ['\Sframe\View\Plugins\Basic', 'if'],
        '#\{\/if\}#' => ['\Sframe\View\Plugins\Basic', 'endIf'],
        '#\{foreach\s+(.+?)\}#' => ['\Sframe\View\Plugins\Basic', 'foreach'],
        '#\{/foreach\}#' => ['\Sframe\View\Plugins\Basic', 'endForeach'],
        '#\{for\s+(.+?)\}#' => ['\Sframe\View\Plugins\Basic', 'for'],
        '#\{/for\}#' => ['\Sframe\View\Plugins\Basic', 'endFor'],
        '#\{block\s+([\w-/]+)\s+(\[.+?\])\}#s' => ['\Sframe\View\Plugins\Block', 'include'],
        '#\{block->(\w+)\}#' => ['\Sframe\View\Plugins\Block', 'var']
    );

    /**
     * register custom compile rules
     * @param array $rules
     */
    public static function register($rules)
    {
        self::$_rules = array_merge(self::$_rules, $rules);
    }

    public function __construct(View $view)
    {
        $this->_view_path = $view->getViewPath();
        $this->_compiled_path = $view->getCompiledPath();
    }


    /**
     * Compile the template
     * @param $template
     * @return void
     */
    public function compile($template)
    {
        $compiled_file = "{$this->_compiled_path}/{$template}";
        $compiled_dir = dirname($compiled_file);
        if (!is_dir($compiled_dir) && !mkdir($compiled_dir, 0755, true)) {
            throw new CompileException('Unable to create view compiled directory:'. $compiled_dir);
        }
        file_put_contents($compiled_file, $this->parse($template));
    }


    /**
     * @param string $template
     * @return string
     */
    public function parse($template)
    {
        $view_file = $this->_view_path .'/'. ltrim($template, '/');
        if (!is_file($view_file)) {
            throw new CompileException('view file is not exists:'. $template);
        }
        $content = file_get_contents($view_file);

        foreach (self::$_rules as $pattern => $handler) {
            $content = preg_replace_callback($pattern, function($matches) use($handler){
                return $this->_handle($handler, $matches);
            }, $content);
        }

        return $content;
    }

    /**
     * @param array $handler
     * @param array $matches
     * @return string
     */
    protected function _handle($handler, $matches)
    {
        $class = $handler[0];
        $method = 'parse'.ucfirst($handler[1]);
        static $plugins = [];
        if (!isset($plugins[$class])) {
            $plugins[$class] = new $class($this);
        }
        return $plugins[$class]->$method($matches);
    }
}