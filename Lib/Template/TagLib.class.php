<?php
/**
 * 标签库基类
 */
abstract class TagLib {
    //模版变量
    protected $templateVar;

    /**
     * 获取当前标签库能处理的标签列表
     * @return array
     */
    abstract public function getTagList();
    //标签定义: block 是否为块标签(true或者false 默认false)

    /**
     * 标签处理程序范例
     * @param  array  $attr    标签属性
     * @param  string $content 标签内容
     * @return string          处理后的标签
     */
    public function _tag($attr, $content = '') {
        return '';
    }

    /**
     * 处理标签
     * @param  string $tagNmae 标签名称
     * @param  array  $tagAttr 标签属性
     * @param  string $content 标签内容
     * @param  bool   $block   是否为块标签
     * @return string          处理后的标签
     */
    public function parseTag($tagNmae, $tagAttr, $content = '', $block = false) {
        $tagName = '_' . $tagNmae;
        if($block) return $this->$tagName($tagAttr, $content); else return $this->$tagName($tagAttr);
    }

    /**
     * 模板变量赋值
     * @param array $templateVar 模板变量
     */
    public function setVar($templateVar) {
        $this->templateVar = $templateVar;
    }
}