<?php
/**
 * 模版引擎类
 */
class Template {
    //模板变量
    private $templateVar = array();

    /**
     * 获取处理后的模板
     * @param  string $templateFile 模板文件路径
     * @param  array  $templateVar  模板变量
     * @return string               编译后的模版Html代码
     */
    public function getTemplate($templateFile, $templateVar) {
        if(file_exists($templateFile)) {
            $this->templateVar = array_merge($this->templateVar, $templateVar);
            //读取模板文件内容
            $tplContent = file_get_contents($templateFile);
            //模板解析
            $tplContent = $this->parse($tplContent);
            //优化生成的php代码
            $tplContent = str_replace('?><?php', '', $tplContent);
            //写缓存文件并记录路径
            $chcheFilePath = Main::writeCacheFile(basename($templateFile, '.tpl') . '.cache.php', $tplContent);
            //模板变量分解成为独立变量
            extract($templateVar, EXTR_OVERWRITE);
            //页面缓存(输出既执行)
            ob_start();
            ob_implicit_flush(0);
            //载入模版缓存文件
            require $chcheFilePath;
            //获取并清空缓存
            $tplContent = ob_get_clean();
            return $tplContent;
        }
        return '';
    }

    /**
     * 模板变量赋值
     * @param  mixed $name  变量名称
     * @param  mixed $value 变量值
     * @return void
     */
    public function assign($name, $value = '') {
        if(is_array($name)) {
            $this->templateVar = array_merge($this->templateVar, $name);
        } else {
            $this->templateVar[$name] = $value;
        }
    }

    //解析模板
    private function parse($content) {
        //内容为空不解析
        if(empty($content)) return '';
        //Html代码优化
        //$content = preg_replace('/>\s+</s', '><', $content);
        //解析模板标签 {value}
        $content = preg_replace_callback('/({)(.+?)(})/m', array($this, 'parseTag'), $content);
        //解析标签
        $content = $this->parseXml($content);
        return $content;
    }

    /**
     * 模版标签解析 {TagName:args[|content]}
     * @param  array $tag 正则匹配后的模版标签
     * @return string      处理后的模版标签
     */
    public function parseTag($tag) {
        if(count($tag) < 4) return '';
        $before = $tag[1];
        $tagStr = $tag[2];
        $after = $tag[3];
        //过滤空格和数字打头的标签
        if(preg_match('/^[\s|\d]/i', $tagStr)) return "{$before}{$tagStr}{$after}";
        $flag = substr($tagStr, 0, 1);
        $flag2 = substr($tagStr, 1, 1);
        $name = substr($tagStr, 1);
        if($flag == '$' && $flag2 != '.' && $flag2 != '(') {//解析模板变量 {$varName}
            return $this->parseVar($name);
        } else if($flag == ':') {//输出某个函数的结果
            return '<?php echo ' . $name . ';?>';
        } else if($flag == '~') {//执行某个函数
            return '<?php ' . $name . ';?>';
        } else if(substr($tagStr, 0, 2) == '//' || (substr($tagStr, 0, 2) == '/*' && substr($tagStr, -2) == '*/')) {//注释标签
            return '';
        } else {//如果都不符合则作为系统变量来解析
            switch($tagStr) {
                case '__PUBLIC__':
                    return __PUBLIC__;
                break;
                case '__ROOT__':
                    return __ROOT__;
                break;
                case 'SERVER_URL':
                    return SERVER_URL . APP_NAME . '/';
                break;

                default:
                    //未识别的标签直接返回
                    return "{$before}{$tagStr}{$after}";
                break;
            }
        }
    }

    //模板变量解析,支持使用函数 {$varname|function1|function2=arg1,arg2}
    private function parseVar($varStr) {
        $varStr = trim($varStr);
        static $_varParseList = array();
        //如果已经解析过该变量字串,则直接返回变量值
        if(isset($_varParseList[$varStr])) return $_varParseList[$varStr];
        if(!empty($varStr) && !isset($varStr)) {
            $varArray = explode('|', $varStr);
            //取得变量名称
            $var = array_shift($varArray);
            if(strpos($var, '.') !== false) {//支持 {$var.property}
                $vars = explode('.', $var);
                $var = array_shift($vars);
                $name = '$' . $var;
                foreach($vars as $val) {
                    $name .= '["' . $val . '"]';
                }
            } else if(strpos($var, '[') !== false) {//支持 {$var['key']} 方式输出数组
                $name = "$" . $var;
                preg_match('/(.+?)\[(.+?)\]/is', $var, $match);
                $var = $match[1];
            } else if(strpos($var, ':') !== false && strpos($var, '(') === false && strpos($var, '::') === false && strpos($var, '?') === false) {//支持 {$var:property} 方式输出对象的属性
                $vars = explode(':', $var);
                $var = str_replace(':', '->', $var);
                $name = "$$var";
                $var = $vars[0];
            } else {
                $name = "$$var";
            }
            if(count($varArray) > 0) {//对变量使用函数
                $name = $this->parseVarFunction($name, $varArray);
            }
            $_varParseList[$varStr] = $parseStr = '<?php echo (' . $name . ');?>';
            return $parseStr;
        }
        return '$' . $varStr;
    }

    //对模板变量使用函数 {$varname|function1|function2=arg1,arg2}
    private function parseVarFunction($name, $varArray) {
        //对变量使用函数
        $length = count($varArray);
        //取得模板禁止使用函数列表 临时
        $template_deny_funs = explode(',', 'evel,exec,system,phpinfo,die,exit');
        for($i = 0; $i < $length; $i++) {
            $args = explode('=', $varArray[$i], 2);
            //模板函数过滤
            $fun = strtolower(trim($args[0]));
            switch($fun) {
                case 'default': //特殊模板函数
                    $name = '@(' . $name . ')?(' . $name . '):' . $args[1];
                break;
                default: //通用模板函数
                    if(!in_array($fun, $template_deny_funs)) {
                        if(isset($args[1])) {
                            if(strstr($args[1], '###')) {
                                $args[1] = str_replace('###', $name, $args[1]);
                                $name = "$fun($args[1])";
                            } else {
                                $name = "$fun($name,$args[1])";
                            }
                        } else if(!empty($args[0])) {
                            $name = "$fun($name)";
                        }
                    }
                break;
            }
        }
        return $name;
    }

    //解析Xml属性为数组
    private function parseXmlAttr($tag) {
        preg_match('/<.+?\s(.*)>/', $tag, $matches);
        if(count($matches) > 1) {
            $attr = $matches[1];
        } else return array();
        preg_match_all('/\s*(.+?)=["\'](.+?)["\']/', $attr, $matches);
        if(count($matches) > 2) {
            $attr = array();
            for($i = 0; $i < count($matches[0]); $i++) {
                $attr += array($matches[1][$i] => $matches[2][$i]);
            }
        }
        return $attr;
    }

    /**
     * 寻找指定的行级Xml标签
     * @param  string $name   要寻找的标签的名字
     * @param  string $str    要寻找的Xml文本
     * @param  int    $offset 搜索偏移量(自动定位到最后位置)
     * @return string         寻找到的标签
     */
    public function findInlineXml($name, $str, &$offset = 0) {
        preg_match('/<' . $name . '\s*.*?\/\s*>/i', $str, $matches, null, $offset);
        if(isset($matches[0])) {
            $offset = strpos($str, $matches[0]);
            return $matches[0];
        } else {
            return '';
        }
    }

    /**
     * 寻找指定的块级Xml标签
     * @param  string $name    要寻找的标签的名字
     * @param  string $str     要寻找的Xml文本
     * @param  int    $offset  搜索偏移量
     * @param  string $content Xml内的文本
     * @return string          寻找到的标签
     */
    public function findBlockXml($name, $str, &$offset = 0, &$content = '') {
        $firstIndex = $this->findBlockXmlFirst($name, $str, $offset, $firstStr);
        if($firstIndex == -1) return '';
        $lastIndex = $this->findBlockXmlLast($name, $str, $firstIndex);
        if($lastIndex == -1) return '';
        if(preg_match("/(<$name.*?>)(.*?)(\\s*<\\/$name>)/is", substr($str, $offset), $matches)) {
            $offset = $lastIndex;
            $content = $matches[2];
            return $matches[1] . $matches[2] . $matches[3];
        } else {
            return '';
        }
    }

    //寻找指定的块级Xml标签的开始
    private function findBlockXmlFirst($name, $str, $offset = 0, &$firstStr = '') {
        if(preg_match('/<' . $name . '[^\/]*?>/i', substr($str, $offset), $matches, PREG_OFFSET_CAPTURE)) {
            $firstStr = $matches[0][0];
            return $matches[0][1] + strlen($firstStr);
        } else {
            return -1;
        }
    }

    //寻找指定的块级Xml标签的结尾
    private function findBlockXmlLast($name, $str, $offset = 0) {
        $firstIndex = $this->findBlockXmlFirst($name, $str, $offset);
        $lastIndex = strpos($str, "</$name>", $offset) + strlen("</$name>");
        if($firstIndex != -1 && $firstIndex < $lastIndex) return $this->findBlockXmlLast($name, $str, $lastIndex); else return $lastIndex;
    }

    //解析标签
    private function parseXml($xml) {
        $tagLibsFile = glob(TPL_PATH . 'TagLib/*TagLib.class.php');
        $tagLibsClass = array();
        foreach($tagLibsFile as $value) {
            Main::import('TagLib/' . getClassName($value));
            $tagLibsClass[] = getClassName($value);
        }
        foreach($tagLibsClass as $key => $value) {
            $tagLib = new $value();
            $tagLib->setVar($this->templateVar);
            foreach($tagLib->getTagList() as $key => $value) {
                $offset = 0;
                if(isset($value['block']) && $value['block'] == true) {
                    while(($tagStr = $this->findBlockXml($key, $xml, $_offset, $content)) != '') {
                        $replaceTagStr = $tagLib->parseTag($key, $this->parseXmlAttr($tagStr), $content, true);
                        $xml = $this->replaceXml($tagStr, $replaceTagStr, $xml, $offset);
                        $offset = $_offset;
                    }
                } else {
                    while(($tagStr = $this->findInlineXml($key, $xml, $offset)) != '') {
                        $replaceTagStr = $tagLib->parseTag($key, $this->parseXmlAttr($tagStr));
                        $xml = $this->replaceXml($tagStr, $replaceTagStr, $xml, $offset);
                    }
                }
            }
        }
        return $xml;
    }

    //替换指定Xml标签
    private function replaceXml($tagStr, $replaceTagStr, $content, $offset = 0) {
        $first = strpos($content, $tagStr, $offset);
        $last = $first + strlen($tagStr);
        return substr($content, 0, $first) . $replaceTagStr . substr($content, $last);
    }
}