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
     * #\{input\s(\w+)(\s+[\w\[\]]+)?(\s+.+?)?\}#
     * e.g. {input text user_name id="user_name" default_value="aaaa"}
     */
    public function parseInput($matches)
    {
        $name = empty($matches[2]) ? '' : trim($matches[2]);
        $str = '<input type="'. $matches[1] .'"';
        $default_value = '';
        $pop_str = '';
        if (!empty($matches[3])) {
            $pop_str .= preg_replace_callback('#default_value="(.+?)"#', function($dv_match) use(&$default_value){
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
}