<?php
/**
 * 视图处理类
 */
class View {
    //模板变量
    private $var = array();

    /**
     * 加载模板并显示页面
     * @param string $templateFile 指定要调用的模板文件名
     * @param string $content      指定要输出的内容
     * @return void
     */
    public function display($templateFile, $content) {
        if($templateFile != '') {
            //解析并获取模板内容
            $content .= $this->fetch($templateFile);
        }
        //显示模板内容
        $this->render($content);
    }

    /**
     * 显示内容文本可以包括Html
     * @param  string $content 显示内容
     * @return void
     */
    public function render($content) {
        //网页字符编码
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Powered-By: MoeHero');
        //显示模板文件
        echo $content;
    }

    /**
     * 解析和获取模板内容
     * @param  string $templateFile 模板文件名
     * @return string
     */
    public function fetch($templateFile) {
        $templateFile = __ROOT__ . APP_NAME . '/' . UrlParse::$model . '/Tpl/' . UrlParse::$controller . '/' . $templateFile;
        if(!file_exists($templateFile)) return '';
        //获取Template类实例
        $template = Main::getInstance('Template');
        $content = $template->getTemplate($templateFile, $this->var);
        //返回模板内容
        return $content;
    }

    /**
     * 模板变量赋值
     * @param  mixed $name  变量名称
     * @param  mixed $value 变量值
     * @return void
     */
    public function assign($name, $value = '') {
        if(is_array($name)) {
            $this->var = array_merge($this->var, $name);
        } else {
            $this->var[$name] = $value;
        }
    }

    /**
     * 取得模板变量的值
     * @param  string $name 变量名称
     * @return mixed
     */
    public function get($name = '') {
        if($name == '') return $this->var;
        return isset($this->var[$name]) ? $this->var[$name] : null;
    }
}