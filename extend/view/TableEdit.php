<?php

namespace view;

use control\Control;
use control\ControlBootstrap;
use think\Exception;
use think\facade\Log;
use think\Response;


/**
 * 内容编辑器
 *
 * 场景：文章、产品等详情编辑
 *
 * Class TableEdit
 * @since v1.0.0
 * @author ranj
 * @package view
 */
class TableEdit extends Table {
    /**
     * 获取表单字段列表
     * @var callable
     */
    protected $fieldsCall;

    /**
     * 右侧 按钮声明
     * @var callable
     */
    protected $action;

    /**
     * 表单提交处理
     * @var callable
     */
    protected $call_submit;

    /**
     * entity 加载处理
     * @var callable
     */
    protected $call_load;

    /**
     * entity 当前编辑对象的实例
     * @var array
     */
    protected $model;

    /**
     * 事件触发地址，默认为当前URL
     *
     * @var string
     */
    protected $url;

    /**
     * 表单渲染器，默认为BootstrapControl
     *
     * @var Control
     */
    protected $control;

    public function __construct($viewKey,$args){
        $args = parse_args($args,array(
            'url'=>null,
            'control'=>new ControlBootstrap(),
            'action'=>function(){},
            'fields'=>function(){}
        ));

        $this->viewKey = $viewKey;
        $this->action = $args['action'];
        $this->fieldsCall = $args['fields'];
        $this->url = $args['url'];
        $this->control = $args['control'];
    }

    /**
     * 获取表单渲染器
     *
     * @return Control
     */
    public function getControl(){
        return $this->control;
    }

    /**
     * 获取事件触发URL
     *
     * @param string $default
     * @return string
     */
    public function getUrl($default=null){
        return $this->url?$this->url:$default;
    }

    /**
     * 获取表单字段，并且组装为tab结构
     *
     * @return array
     */
    public function getFields(){
        $fields = $this->fieldsCall?call_user_func_array($this->fieldsCall,array($this->getModel(),$this)):null;
        if(array_is_empty($fields)){
            return array();
        }

        $control = $this->control;
        foreach ($fields as $groupKey =>$group){
            if(!is_string($group['options'])&&is_callable($group['options'])){
                continue;
            }
            if(!$this->model){
                continue;
            }
            $optionFields = $group['options'];
            $control->setValues($optionFields,$this->model);
            $fields[$groupKey]['options'] = $optionFields;
        }

        return $fields;
    }

    /**
     * 获取表单按钮列表
     *
     * @return array
     */
    public function getActionList(){
        $action = $this->action?call_user_func($this->action,$this->getModel()):null;
        return $action&&is_array($action)&&count($action)?$action:null;
    }

    /**
     * 注册表单提交事件
     * @param callable $call 表单提交
     */
    public function handleSubmit($call){
        $this->call_submit = $call;
    }

    /**
     * 注册表单加载事件
     * @param callable $call 表单entity加载
     */
    public function handleLoad($call){
        $this->call_load = $call;
    }

    /**
     * 执行表单提交事件
     * @return bool|mixed|Response
     * @throws \Exception
     */
    protected function do_submit(){
        if(!$this->call_submit){return false;}

        if(!request()->isAjax()){
            return false;
        }

        $_ = request()->param("__");
        if($_!=$this->getViewKey("form_")){
            return false;
        }

        $fields = $this->getFields();
        if(!$fields){
            return false;
        }

        $res = array();
        foreach ($fields as $groupKey=>$group){
            if(!is_string($group['options'])&&is_callable($group['options'])){
                continue;
            }

            $ctrl = $this->control;
            foreach ($group['options'] as $fieldKey=>$field){
                try{
                    $response = $ctrl->param($fieldKey,$field,$res,function($key){
                        return request()->param($key);
                    });

                    if($response instanceof Response){
                        return $response;
                    }
                }catch (\Exception $e){
                    Log::error($e);
                    return errorJson(500,$e->getMessage());
                }
            }
        }
       try{
           return call_user_func_array($this->call_submit,array($res,$this->getModel()));
       }catch (\Exception $e){
            Log::error($e);
            throw $e;
       }
    }


    /**
     * 获取表单entity(实例对象)
     *
     * @return array
     */
    public function getModel(){
        return $this->model;
    }

    /**
     * 执行表单加载事件
     * @return bool|mixed
     */
    public function doLoad(){
        if(!$this->call_load) {
            return false;
        }

        $res = call_user_func($this->call_load);
        if ($res instanceof Response) {
            return $res;
        }
        $this->model = $res;
        return false;
    }

    /**
     * 加载模板输出
     * @param  string $template 模板文件名
     * @param  array $vars 模板输出变量
     * @param  array $config 模板参数
     * @return mixed
     * @throws \Exception
     */
    public function fetch($template = '', $vars = [], $config = []){
        $this->assign($vars);

        $res = $this->doLoad();
        if($res instanceof Response){
            return $res;
        }

        $res = $this->do_submit();
        if($res instanceof Response){
            return $res;
        }

        return Response::create($template, 'view')->assign($vars)->config($config);
    }

}