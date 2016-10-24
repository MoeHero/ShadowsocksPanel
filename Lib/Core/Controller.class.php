<?php
/**
 * 控制器基类
 */
class Controller {
    //View类实例
    private $view = null;

    /**
     * 模板显示 调用内置的模板引擎显示方法
     * @param  string $templateFile 指定要调用的模板文件 默认为空 使用当前控制器名称的模版
     * @param  string $extension    指定模版文件扩展名
     * @return void
     */
    protected function display($templateFile = 'Index', $extension = 'tpl') {
        $this->view = $this->view ?: Main::getInstance('View');
        $this->view->display("{$templateFile}.{$extension}", '');
    }

    /**
     * 输出内容文本可以包括Html
     * @param  string $content 输出内容
     * @return void
     */
    protected function show($content) {
        $this->view = $this->view ?: Main::getInstance('View');
        $this->view->display('', $content);
    }

    /**
     * 模板变量赋值
     * @param  string $name  要显示的模板变量
     * @param  string $value 变量的值
     */
    public function assign($name, $value) {
        $this->view = $this->view ?: Main::getInstance('View');
        $this->view->assign($name, $value);
    }

    public function __set($name, $value) {
        $this->assign($name, $value);
    }

    /**
     * 取得模板显示变量的值
     * @param  string $name 模板显示变量
     * @return mixed
     */
    public function get($name = '') {
        $this->view = $this->view ?: Main::getInstance('View');
        return $this->view->get($name);
    }

    public function __get($name) {
        return $this->get($name);
    }

    /**
     * 检测模板变量的值
     * @param  string $name 名称
     * @return bool
     */
    public function __isset($name) {
        return $this->get($name);
    }
}