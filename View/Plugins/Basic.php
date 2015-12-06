<?php namespace Sframe\View\Plugins;

class Basic extends PluginAbstract
{
    /**
     * #\{//\s+(.+?)\}#
     * e.g. {// $test}
     */
    public function parseComment($matches)
    {
        return '<?php // '. $matches[1] .' ?>';
    }

    /**
     * #\{\$([a-zA-Z_].+?)\}#
     * e.g. {$test}, {$user['name']}, {$this->hello('aa', 'bb')}
     */
    public function parseVar($matches)
    {
        return '<?php echo $'. $matches[1] .'; ?>';
    }

    /**
     * #\{include\s+(.+?)\}#
     * e.g. {include part/head.php}
     */
    public function parseInclude($matches)
    {
        return $this->_compiler->parse($matches[1]);
    }

    /**
     * #\{trans\s+(.+?)\}#
     * e.g. {trans $title}
     */
    public function parseTrans($matches)
    {
        return gettext($matches[1]);
    }

    /**
     * #\{escape\s+(.+?)\}#
     * e.g. {trans $title}
     */
    public function parseEscape($matches)
    {
        return '<?php echo htmlspecialchars('.$matches[1].', ENT_QUOTES | ENT_SUBSTITUTE); ?>';
    }

    /**
     * #\{if\s+(.+?)\}#
     * #\{\/if\}#
     * e.g.
     * {if $aa == 1}
     * {/if}
     */
    public function parseIf($matches)
    {
        return '<?php if('. $matches[1] .'): ?>';
    }
    public function parseEndIf($matches)
    {
        return '<?php endif; ?>';
    }


    /**
     * #\{foreach\s+(.+?)\}#
     * #\{/foreach\}#
     * e.g.
     * {foreach $test as $k=>$v}
     * {/foreach}
     */
    public function parseForeach($matches)
    {
        return '<?php foreach('. $matches[1] .'): ?>';
    }
    public function parseEndForeach($matches)
    {
        return '<?php endforeach; ?>';
    }

    /**
     * #\{for\s+(.+?)\}#
     * #\{/for\}#
     * e.g.
     * {for $i=1;$i<10;$i++}
     * {/for}
     */
    public function parseFor($matches)
    {
        return '<?php for('. $matches[1] .'): ?>';
    }
    public function parseEndFor($matches)
    {
        return '<?php endfor; ?>';
    }


}