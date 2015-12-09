<?php namespace Sframe\View;

class FormParser extends ParserAbstract
{
    /**
     * #\{form(\s+data=\$(\w+))?(\s+.+?)?\}#
     * e.g. {form data=$test method="post" action="test/submit"}
     */
    public function parseForm($matches)
    {
        $str = '';
        if (!empty($matches[2])) {
            $str .= '<?php $_form=$'. $matches[2] .'; ?>';
        } else {
            $str .= '<?php $_form=[]; ?>';
        }
        $str .= '<form';
        if (!empty($matches[3])) {
            $str .= ' '.preg_replace_callback('#action="(.+?)"#', function($action_match){
                if (strpos($action_match[1], '?')) {
                    $uri = strstr($action_match[1], '?', true);
                    $params = substr(strstr($action_match[1], '?'), 1);
                } else {
                    $uri = $action_match[1];
                    $params = null;
                }
                $url = $this->_compiler->getView()->getRouter()->route($uri, $params);
                return 'action="'. $url .'"';
            }, trim($matches[3]));
        }
        $str .= '>';
        $str .= '<input type="hidden" name="_csrf" value="<?php echo \Sframe\Html\Csrf::getToken(); ?>">';
        return $str;
    }

    /**
     * #\{/form\}#
     * e.g. {/form}
     */
    public function parseEndForm($matched)
    {
        return '</form>';
    }

    /**
     * #\{input\s(\w+)(\s+[\w\[\]]+)?(\s+.+?)?\}#
     * e.g. {input text user_name id="user_name" default="aaaa"}
     */
    public function parseInput($matches)
    {
        $name = empty($matches[2]) ? '' : trim($matches[2]);
        $str = '<input type="'. $matches[1] .'"';
        $default_value = '';
        $pop_str = '';
        if (!empty($matches[3])) {
            $pop_str .= preg_replace_callback('#default="(.+?)"#', function($dv_match) use(&$default_value){
                $default_value = $dv_match[1];
                return '';
            }, trim($matches[3]));
        }
        if ($name) {
            $str .= ' name="'. $name .'"';
            $pop_str .= ' value="<?php echo empty($_form[\''. $name .'\']) ? \''. $default_value .'\' : $_form[\''. $name .'\']; ?>"';
        }
        if ($pop_str) {
            $str .= ' '. $pop_str;
        }
        $str .= '>';
        return $str;
    }

    /**
     * #\{textarea(\s+[\w\[\]]+)?(\s+.+?)?\}#
     * e.g. {textarea name id="test"}
     */
    public function parseTextarea($matches)
    {
        $name = empty($matches[1]) ? '' : trim($matches[1]);
        $str = '<textarea';
        if ($name) {
            $str .= ' name="'. $name .'"';
        }
        if (!empty($matches[2])) {
            $str .= ' '. trim($matches[2]);
        }
        $str .= '><?php echo isset($_form[\''. $name .'\'])?$_form[\''. $name .'\']:\'\'; ?></textarea>';
        return $str;
    }

    /**
     * #\{select(\s+data=(\$\w+|\[.+?\]))(\s[\w\[\]]+)?(\s+.+?)?\}#
     * e.g. {select data=$hello name id="test" default="a" first=[0, 'please select']}
     * e.g. {select data=['a' => 'aa', 'b' => 'bb'] name id="text" first="please select"}
     */
    public function parseSelect($matches)
    {
        $name = empty($matches[3]) ? '' : trim($matches[3]);
        $str = '';
        $str .= '<?php $_select='. $matches[2] .'; ?>';
        $str .= '<select';
        if ($name) {
            $str .= ' name="'. $name .'"';
        }
        $first_value = '';
        $first_text = '';
        $default_selected = '';
        if (!empty($matches[4])) {
            $pop_str = preg_replace_callback('#first=\[(.+?)\]#', function($imatch) use(&$first_value, &$first_text){
                    $v = '['. str_replace("'", '"', $imatch[1]) .']';
                    $v = json_decode($v);
                    $first_value = $v[0];
                    $first_text = $v[1];
                    return '';
                }, trim($matches[4]));
            $pop_str = preg_replace_callback('#first="(.+?)"#', function($imatch) use(&$first_text){
                    $first_text = $imatch[1];
                    return '';
                }, $pop_str);
            $pop_str = preg_replace_callback('#default="(.+?)"#', function($imatch) use(&$default_selected){
                    $default_selected = $imatch[1];
                }, $pop_str);
            $str .= ' '. $pop_str;
        }
        $str .= '>';
        if ($first_text) {
            $str .= '<option value="'. $first_value .'">'. $first_text .'</option>';
        }
        if ($default_selected) {
            $str .= '<?php if(!isset($_form[\''. $name .'\']))$_form[\''. $name .'\']=\''. $default_selected .'\'; ?>';
        }
        $str .= '<?php foreach($_select as $k=>$v):?>';
        $str .= '<option value="<?php echo $k; ?>"<?php if(!empty($_form[\''. $name .'\']) && $_form[\''. $name .'\']==$k)echo \' selected\';?>><?php echo $v; ?></option>';
        $str .= '<?php endforeach; ?>';
        $str .= '</select>';
        return $str;
    }

    /**
     * #\{checkbox(\s+data=(\$\w+|\[.+?\]))(\s[\w\[\]]+)?(\s+.+?)?\}#
     * e.g. {checkbox data=$hello name class="checkbox" default=['a', 'b']}
     * e.g. {checkbox data=['a'=>'aa', 'b'=>'bb'] name class="text"}
     */
    public function parseCheckbox($matches)
    {
        $name = empty($matches[3]) ? '' : trim($matches[3]);
        $name_str = $name ? (' name="'. $name .'[]"') : '';
        $str = '';
        $str .= '<?php $_checkbox='. $matches[2] .'; ?>';
        $default_checked = '';
        $pop_str = '';
        if (!empty($matches[4])) {
            $pop_str = ' '.preg_replace_callback('#default=\[(.+?)\]#', function($imatch) use(&$default_checked){
                    $default_checked = $imatch[1];
                    return '';
                }, trim($matches[4]));
        }
        if ($default_checked) {
            $str .= '<?php if(!isset($_form[\''. $name .'\']))$_form[\''. $name .'\']=['. $default_checked .']; ?>';
        }
        $str .= '<?php foreach($_checkbox as $k=>$v):?>';
        $str .= '<label class="checkbox_label"><input type="checkbox"'. $name_str . $pop_str .' value="<?php echo $k; ?>"<?php if(!empty($_form[\''. $name .'\']) && in_array($k,$_form[\''. $name .'\']))echo \' checked\'; ?>><span class="check_span"><?php echo $v; ?></span></label>';
        $str .= '<?php endforeach; ?>';
        return $str;
    }

    /**
     * #\{radio(\s+data=(\$\w+|\[.+?\]))(\s[\w\[\]]+)?(\s+.+?)?\}#
     * e.g. {radio data=$hello name class="checkbox" default="a"}
     * e.g. {radio data=['a'=>'aa', 'b'=>'bb'] name class="text"}
     */
    public function parseRadio($matches)
    {
        $name = empty($matches[3]) ? '' : trim($matches[3]);
        $name_str = $name ? (' name="'. $name .'"') : '';
        $str = '';
        $str .= '<?php $_radio='. $matches[2] .'; ?>';
        $default_checked = '';
        $pop_str = '';
        if (!empty($matches[4])) {
            $pop_str = ' '.preg_replace_callback('#default="(.+?)"#', function($imatch) use(&$default_checked){
                    $default_checked = $imatch[1];
                    return '';
                }, trim($matches[4]));
        }
        if ($default_checked) {
            $str .= '<?php if(!isset($_form[\''. $name .'\']))$_form[\''. $name .'\']=\''. $default_checked .'\'; ?>';
        }
        $str .= '<?php foreach($_radio as $k=>$v):?>';
        $str .= '<label class="radio_label"><input type="radio"'. $name_str . $pop_str .' value="<?php echo $k; ?>"<?php if(!empty($_form[\''. $name .'\']) && $_form[\''. $name .'\']==$k)echo \' checked\'; ?>><span class="radio_span"><?php echo $v; ?></span></label>';
        $str .= '<?php endforeach; ?>';
        return $str;
    }
}