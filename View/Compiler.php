<?php namespace Sframe\View;

class Compiler
{
    /**
     * @var View
     */
    protected $_view;

    protected static $_rules = array(
        '#\{\$([a-zA-Z_].+?)\}#' => ['\Sframe\View\BasicParser', 'var'],
        '#\{\/\/\s+(.+?)\}#' => ['\Sframe\View\BasicParser', 'comment'],
        '#\{include\s+(.+?)\}#' => ['\Sframe\View\BasicParser', 'include'],
        '#\{trans\s+(.+?)\}#' => ['\Sframe\View\BasicParser', 'trans'],
        '#\{escape\s+(.+?)\}#' => ['\Sframe\View\BasicParser', 'escape'],
        '#\{if\s+(.+?)\}#' => ['\Sframe\View\BasicParser', 'if'],
        '#\{\/if\}#' => ['\Sframe\View\BasicParser', 'endIf'],
        '#\{foreach\s+(.+?)\}#' => ['\Sframe\View\BasicParser', 'foreach'],
        '#\{/foreach\}#' => ['\Sframe\View\BasicParser', 'endForeach'],
        '#\{for\s+(.+?)\}#' => ['\Sframe\View\BasicParser', 'for'],
        '#\{/for\}#' => ['\Sframe\View\BasicParser', 'endFor'],
        '#\{block\s+([\w-/]+)\s+(\[.+?\])\}#s' => ['\Sframe\View\BasicParser', 'block'],
        '#\{block->(\w+)\}#' => ['\Sframe\View\BasicParser', 'blockVar'],
        '#\{route\s+([\w-/]+)(\s.+)?\}#' => ['\Sframe\View\BasicParser', 'route'],
        '#\{form(\s+data=\$(\w+))?(\s+(.+?))?\}#' => ['\Sframe\View\FormParser', 'form'],
        '#\{input\s(\w+)(\s+[\w\[\]]+)?(\s+.+?)?\}#' => ['\Sframe\View\FormParser', 'input'],

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
        $this->_view = $view;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->_view;
    }


    /**
     * Compile the template
     * @param $template
     * @return void
     */
    public function compile($template)
    {
        $compiled_file = $this->_view->getCompiledPath() .'/'. $template;
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
        $view_file = $this->_view->getViewPath() .'/'. ltrim($template, '/');
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
            if (!$plugins[$class] instanceof ParserAbstract) {
                throw new CompileException('invalid plugin class, it should be extends ParserAbstract');
            }
        }
        return $plugins[$class]->$method($matches);
    }
}