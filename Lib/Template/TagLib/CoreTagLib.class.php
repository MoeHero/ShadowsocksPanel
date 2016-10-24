<?php
class CoreTagLib extends TagLib {
    //处理标签定义
    private $tags = array(
        //标签定义: block 是否为块标签(true或者false 默认false)
        'js' => array(),
        'css' => array(),
        'if' => array('block' => true),
        'foreach' => array('block' => true),
        'include' => array()
    );

    /**
     * 获取当前标签库能处理的标签列表
     * @return array
     */
    public function getTagList() {
        return $this->tags;
    }

    /**
     * js标签处理程序
     * @param  array $attr 标签属性
     * @return string       处理后的标签
     */
    public function _js($attr) {
        $tag = '<script';
        //将属性转换为单个变量
        extract($attr);
        $parse = !isset($parse) ? true : $parse == 'true' ? true : false;
        if(isset($src)) {
            $ok = false;
            //定义文件名称
            $filename = basename($src);
            if(file_exists($src)) {
                if($parse) {//如果需要解析则解析否则不处理
                    //读取模板文件内容
                    $jsContent = file_get_contents($src);
                    //js解析
                    $jsContent = $this->parseJs($jsContent);
                    //优化生成的php代码
                    $jsContent = str_replace('?><?php', '', $jsContent);
                    //写缓存文件并记录路径
                    $chcheFilename = Main::writeCacheFile($filename, $jsContent);
                    //模板变量分解成为独立变量(和模版类一样的变量)
                    extract($this->templateVar, EXTR_OVERWRITE);
                    //页面缓存(输出既执行)
                    ob_start();
                    ob_implicit_flush(0);
                    //载入模版缓存文件
                    require $chcheFilename;
                    //获取并清空缓存
                    $jsContent = ob_get_clean();
                    //把运行完结果写入缓存文件
                    $ok = file_put_contents($chcheFilename, $jsContent) != 0;
                } else {
                    $cacheFilename = __ROOT__ . CAHCE_PATH . $filename;
                    //如果文件不存在或md5不同则复制
                    if(!file_exists($cacheFilename) || md5_file($src) != md5_file($cacheFilename)) {
                        $ok = copy($src, $cacheFilename);
                    } else {
                        $ok = true;
                    }
                }
            }
            //如果ok则返回缓存路径,否则判断前两个字符是否为//如果是则返回src否则输出Error
            $src = $ok ? '/' . CAHCE_PATH . $filename : (substr($src, 0, 2) == '//' ? $src : 'Error');
            $tag .= " src=\"$src\"";
        }
        $tag .= ' type="text/javascript"></script>';
        return $tag;
    }

    //js解析
    private function parseJs($content) {
        //内容为空不解析
        if(empty($content)) return '';
        //获取Template类实例
        $template = Main::getInstance('Template');
        //设置Template变量
        $template->assign($this->templateVar);
        //解析模板标签 <<value>>
        return preg_replace_callback('/(<<)(.+?)(>>)/m', array($template, 'parseTag'), $content);
    }

    /**
     * css标签处理程序
     * @param  array $attr 标签属性
     * @return string       处理后的标签
     */
    public function _css($attr) {
        $tag = '<link';
        //将属性转换为单个变量
        extract($attr);
        $parse = !isset($parse) ? true : $parse == 'true' ? true : false;
        if(isset($src)) {
            $ok = false;
            //定义文件名称
            $filename = basename($src);
            if(file_exists($src)) {
                if($parse) {//如果需要解析则解析否则不处理
                    //读取模板文件内容
                    $jsContent = file_get_contents($src);
                    //css解析
                    $jsContent = $this->parseCss($jsContent);
                    //优化生成的php代码
                    $jsContent = str_replace('?><?php', '', $jsContent);
                    //写缓存文件并记录路径
                    $chcheFilename = Main::writeCacheFile($filename, $jsContent);
                    //模板变量分解成为独立变量(和模版类一样的变量)
                    extract($this->templateVar, EXTR_OVERWRITE);
                    //页面缓存(输出既执行)
                    ob_start();
                    ob_implicit_flush(0);
                    //载入模版缓存文件
                    require $chcheFilename;
                    //获取并清空缓存
                    $jsContent = ob_get_clean();
                    //把运行完结果写入缓存文件
                    $ok = file_put_contents($chcheFilename, $jsContent) != 0;
                } else {
                    $cacheFilename = __ROOT__ . CAHCE_PATH . $filename;
                    //如果文件不存在或md5不同则复制
                    if(!file_exists($cacheFilename) || md5_file($src) != md5_file($cacheFilename)) {
                        $ok = copy($src, $cacheFilename);
                    } else {
                        $ok = true;
                    }
                }
            }
            //如果ok则返回缓存路径,否则判断前两个字符是否为//如果是则返回src否则输出Error
            $src = $ok ? '/' . CAHCE_PATH . $filename : (substr($src, 0, 2) == '//' ? $src : 'Error');
            $tag .= " href=\"$src\"";
        }
        $tag .= ' rel="stylesheet"/>';
        return $tag;
    }

    //css解析
    private function parseCss($content) {
        //内容为空不解析
        if(empty($content)) return '';
        //获取Template类实例
        $template = Main::getInstance('Template');
        //设置Template变量
        $template->assign($this->templateVar);
        //解析模板标签 <<value>>
        return preg_replace_callback('/(<<)(.+?)(>>)/m', array($template, 'parseTag'), $content);
    }

    /**
     * if标签处理程序
     * @param  array  $attr    标签属性
     * @param  string $content 标签内容
     * @return string          处理后的标签
     */
    public function _if($attr, $content) {
        //将属性转换为单个变量
        extract($attr);
        if(false !== strpos($content, '<else>')) {
            list($true, $false) = explode('<else>', $content, 2);
        } else {
            list($true, $false) = array($content, '');
        }
        if(isset($var) && isset($this->templateVar[$var])) {
            if(true === $this->templateVar[$var]) {
                return $true;
            } else {
                return $false;
            }
        }
        return $false;
    }

    /**
     * foreach标签处理程序
     * @param  array  $attr    标签属性
     * @param  string $content 标签内容
     * @return string          处理后的标签
     */
    public function _foreach($attr, $content) {
        //将属性转换为单个变量
        extract($attr);
        $return = '';
        if(isset($var) && isset($this->templateVar[$var]) && is_array($this->templateVar[$var])) {
            foreach($this->templateVar[$var] as $k => $v) {
                $return .= $content;
                if(isset($value)) {
                    $return = preg_replace('/\[\$' . $value . '\]/i', $v, $return);
                }
                if(isset($key)) {
                    $return = preg_replace('/\[\$' . $key . '\]/i', $k, $return);
                }
            }
        }
        return $return;
    }

    /**
     * include标签处理程序
     * @param  array $attr 标签属性
     * @return string       处理后的标签
     */
    public function _include($attr) {
        //将属性转换为单个变量
        extract($attr);
        if(isset($file)) {
            //获取Template实例
            $template = Main::getInstance('Template');
            if(strpos($file, ':') !== false) {
                $file = explode(':', $file);
                $filename = TPL_PATH . $file[0];
                for($i = 1; $i < count($file) - 1; $i++) {
                    $filename .= '/' . $file[$i];
                }
            } else {
                $filename = TPL_PATH . $file;
            }
            return $template->getTemplate($filename, $this->templateVar);
        }
    }
}
