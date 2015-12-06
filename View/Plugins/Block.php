<?php namespace Sframe\View\Plugins;

class Block extends PluginAbstract
{
    /**
     * #\{block\s+([\w-/]+)\s+(\[.+?\])\}#s
     * e.g. {block head ['title' => 'test']}
     */
    public function parseInclude($matches)
    {
        return '<?php $_block='. $matches[2] .'; ?>' ."\n". $this->_compiler->parse($matches[1] .'.php');
    }

    /**
     * #\{block->(\w+)\}#
     * e.g. {block->title}
     */
    public function parseVar($matches)
    {
        return '<?php echo isset($_block[\''. $matches[1] .'\']) ? $_block[\''. $matches[1] .'\'] : \'\'; ?>';
    }
}