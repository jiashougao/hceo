<?php
namespace view;

use think\Response;

/**
 * 组件渲染器
 *
 * 系统对数据列表，数据编辑，表单，excel上传导入，网页设计器等统一封装
 *
 * Class Table
 * @since v1.0.0
 * @author ranj
 * @package view
 */
abstract class Table
{
    /**
     * 组件ID
     * 同一个网页内可能存在多个组件，使用ID来分别标识
     * @var string
     */
    protected $viewKey;

    /**
     * 获取组件ID
     * @param string $prefix 组件ID前缀
     * @return string
     */
    public function getViewKey($prefix=''){
        return $prefix.$this->viewKey;
    }

    /**
     * 变量
     * 向网页内声明并绑定变量
     * @param $vars
     */
    public function assign(&$vars){
        $vars[$this->getViewKey()] = $this;
    }

    /**
     * 加载模板并输出
     *
     * @access protected
     * @param  string $template 模板文件名
     * @param  array  $vars     模板输出变量
     * @param  array  $config   模板参数
     * @return Response
     */
    abstract function fetch($template = '', $vars = [], $config = []);
}