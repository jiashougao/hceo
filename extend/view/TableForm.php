<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/1/15
 * Time: 15:04
 */

namespace view;


use control\Control;
use control\ControlBootstrap;
use think\Response;

/**
 * 表单构建组件
 *
 * Class TableForm
 * @since v1.0.0
 * @author ranj
 * @package view
 */
class TableForm extends Table
{
    /**
     * 表单渲染器
     * @var Control
     */
    protected $control;

    /**
     * 表单字段获取
     * @var callable
     */
    protected $fieldsCall;

    public function __construct($viewKey, $args){
        $args = parse_args($args,array(
            'control'=>new ControlBootstrap(),
            'fields'=>null,
        ));

        $this->viewKey = $viewKey;
        $this->control = $args['control'];
        $this->fieldsCall = $args['fields'];
    }

    /**
     * 获取表单字段
     * @return array
     */
    public function getFields(){
        $fields = $this->fieldsCall?call_user_func_array($this->fieldsCall,array($this)):null;
        return array_is_empty($fields)?array():$fields;
    }

    /**
     * 获取表单渲染器
     * @return Control
     */
    public function getControl(){
        return $this->control;
    }

    /**
     * 加载模板输出
     * @access protected
     * @param  string $template 模板文件名
     * @param  array $vars 模板输出变量
     * @param  array $config 模板参数
     * @return Response
     */
    function fetch($template = '', $vars = [], $config = [])
    {
        $this->assign($vars);
        return Response::create($template, 'view')->assign($vars)->config($config);
    }
}