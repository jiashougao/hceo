<?php
namespace control;

use control\fields\Base;
use think\Exception;
use think\Response;

/**
 * 表单字段渲染器
 *
 * 主要功能：1.表单字段渲染为HTML，
 *           2.表单提交，数据接收，验证，存储
 *
 * Class Control
 * @since v1.0.0
 * @author ranj
 * @package control
 */
class Control{

    /**
     * 复杂结构的fields数据为单一的columns列表
     * columns:表单字段列表
     * fields:有row等复杂的结构的columns,如：横排内多个字段布局
     *
     * @param array $fields => fields数据
     * @param callable $filter 字段过滤器（可能需要过滤一些不需要的字段）
     * @param array $columns 默认的columns
     * @return array
     */
    public static function fieldsToColumns($fields,$filter=null,$columns=[]){
        if(!$fields||!is_array($fields)){
            return $fields;
        }

        foreach ($fields as $fieldKey=>$field){
            if(isset($field['type'])&&$field['type']==='row'){
                if(isset($field['items'])&&is_array($field['items'])){
                    foreach ($field['items'] as $itemKey=>$item){
                        if($filter&&!call_user_func($filter,$item,$itemKey)){
                            continue;
                        }
                        $columns[$itemKey] = $item;
                    }
                }
                continue;
            }
            if($filter&&!call_user_func($filter,$field,$fieldKey)){
                continue;
            }
            $columns[$fieldKey] = $field;
        }

        return $columns;
    }

    /**
     * 根据字段的配置 "type" 映射到对应class
     *
     * @param string $type
     * @return string class 文件名
     */
    public function getFieldClass($type){
        $class = strtoupper($type[0]).substr($type,1);
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $class);

        return "\\control\\fields\\".$name;
    }


    /**
     * 从call中获取数据，验证，绑定到fields(数据集)内，并返回
     *
     * @param array $fields 字段值列表
     * @param callable $call 数据获取源
     * @return array 返回已验证的数据集
     *
     * @throws Exception 字段验证失败，将会抛出对应的异常消息
     */
    public function validate($fields,$call=null){
        if(!$fields||!is_array($fields)){
            return array();
        }
        if(!$call){
            $call = function($key){
                return request()->param($key);
            };
        }

        $results = array();
        foreach ($fields as $fieldKey=>$field){
            if(empty($field['type'])){
                $field['type'] ="text";
            }

            $class = $this->getFieldClass($field['type']);
            if(!class_exists($class)){
                $results[$fieldKey] = $call($fieldKey);
                continue;
            }

            $obj = new $class($fieldKey,$field);
            $obj->{'param'}($results,$call);
        }

        return $results;
    }

    /**
     * 根据字段名($key)和字段配置($data) 生成字段HTML
     * 输出HTML数据到ob缓存中
     *
     * @param string $form_id 表单ID
     * @param string $key 字段名
     * @param array $data 字段配置信息
     */
    public function generateField($form_id,$key,$data,$values=[]){
        $type = empty($data['type'])?'text':$data['type'];
        if(!is_string($type)&& is_callable($type)){
            $res = call_user_func_array($type,array($values,false,$this,$form_id));
            if($res &&$res instanceof \think\Response){
                echo $res->getContent();
            }
            return;
        }

        $class = $this->getFieldClass($type);
        if(!class_exists($class)){
            return;
        }

        $res = call_user_func_array(array(new $class($key,$data),'generateField'),array($form_id));
        if($res &&$res instanceof \think\Response){
            echo $res->getContent();
        }
    }

    /**
     * 根据字段名($key)和字段配置($data) 映射字段class
     *
     * @param string $key 字段名
     * @param array $data 字段配置信息
     * @return Base 字段class
     */
    public function getField($key,$data){
        $type = empty($data['type'])?'text':$data['type'];
        if(!is_string($type)&& is_callable($type)){
            return null;
        }

        $class = $this->getFieldClass($type);
        if(!class_exists($class)){
            return null;
        }

        return new $class($key,$data);
    }

    /**
     * @param $key
     * @param $data
     * @param $res
     * @param null $call
     * @throws Exception
     * @return mixed|Response
     */
    public function param($key,$data,&$res,$call=null){
        $type = empty($data['type'])?'text':$data['type'];
        if(!is_string($type)&& is_callable($type)){
            $res[$key] = request()->param($key);
            return $res[$key];
        }

        $class = $this->getFieldClass($type);
        if(!class_exists($class)){
            $res[$key] = request()->param($key);
            return $res[$key];
        }

        $class = new $class($key,$data);
        return $class->{'param'}($res,$call);
    }

    /**
     * 对字段进行数据绑定
     * @param $fields
     * @param $values
     */
    public function setValues(&$fields,$values){
        if(array_is_empty($fields)){
            return;
        }

        foreach ($fields as $fieldKey=>$field){
            $this->setValue($fieldKey,$field,$values);
            $fields[$fieldKey] = $field;
        }
    }

    /**
     * 设置值
     *
     * @param $fieldKey
     * @param $field
     * @param $values
     */
    private function setValue($fieldKey,&$field,$values){
        $type = empty($field['type'])?'text':$field['type'];
        if(!is_string($type)&& is_callable($type)){
            $default = isset($field['default'])?$field['default']:null;
            $field['value'] = isset($values[$fieldKey])?$values[$fieldKey]:$default;
            return;
        }

        $class = $this->getFieldClass($type);
        if(!class_exists($class)){
            $default = isset($field['default'])?$field['default']:null;
            $field['value'] = isset($values[$fieldKey])?$values[$fieldKey]:$default;
            return;
        }

        $class = new $class($fieldKey,$field);
       $class->{'setValue'}($field,$values);
    }

    /**
     * 预览数据
     * 调用此API之前，请调用setValue
     *
     * @param $key
     * @param $data
     * @param $values
     * @param bool $strip_tags 是否去除html
     * @return mixed
     */
    public function preview($key,$data,$values,$strip_tags=false){
        $this->setValue($key,$data,$values);

        $type = empty($data['type'])?'text':$data['type'];
        if(!empty($data['preview'])){
            $res = call_user_func($data['preview'],$values,$strip_tags,$this,'model'/*表单ID*/);
            if($res &&$res instanceof Response){
                return $res->getContent();
            }
            return $res;
        }

        if(!is_string($type)&& is_callable($type)){
            ob_start();
            $res = call_user_func_array($type,array($values,$strip_tags,$this,'model'/*表单ID*/));
            if($res &&$res instanceof Response){
                echo $res->getContent();
            }
            $celVal= ob_get_clean();

            return $strip_tags?strip_all_tags($celVal,true):$celVal;
        }

        $class = $this->getFieldClass($type);
        if(!class_exists($class)){
            if(!isset($data['value'])){$data['value']=isset($data['default'])?$data['default']:null;}
            return $strip_tags?trim(strip_all_tags($data['value'],true)):$data['value'];
        }

        $class = new $class($key,$data);
        return $class->{'preview'}($strip_tags,$values);
    }
}