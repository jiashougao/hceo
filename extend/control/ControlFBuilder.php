<?php
namespace control;

use think\Exception;

/**
 * “表单构建器” 渲染器
 *
 * “表单构建器”：表单数据存储，问券信息存储等
 *
 * Class ControlFBuilder
 * @since v1.0.0
 * @author ranj
 * @package control
 */
class ControlFBuilder extends Control{
    public function getFieldClass($type){
        $class = strtoupper($type[0]).substr($type,1);
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $class);

        return "\\control\\fields_fbuilder\\".$name;
    }

    /**
     * 获取表单构建器所有可用的组件
     *
     * @param array $allowed_components 允许使用的表单组件
     * @param array $disabled_components 禁止使用的表单组件
     * @return array
     */
    public function getComponents($allowed_components=array(),$disabled_components=array()){
        if(!$allowed_components||!is_array($allowed_components)){
            $allowed_components = array();
        }

        if(!$disabled_components||!is_array($disabled_components)){
            $disabled_components = array();
        }

        $default =  array(
            'base'=>array(
                'title'=>'基础',
                'options'=>array(
                    'text'=>array(
                        'title'=>'输入框',
                        'class'=>"\\control\\fields_fbuilder\\Text"
                    ),
                    'password'=>array(
                        'title'=>'密码输入框',
                        'class'=>"\\control\\fields_fbuilder\\Password"
                    ),
                    'textarea'=>array(
                        'title'=>'多行输入框',
                        'class'=>"\\control\\fields_fbuilder\\Textarea"
                    ),
                    'hidden'=>array(
                        'title'=>'隐藏字段',
                        'class'=>"\\control\\fields_fbuilder\\Hidden"
                    ),
                    'checkbox'=>array(
                        'title'=>'是/否',
                        'class'=>"\\control\\fields_fbuilder\\Checkbox"
                    ),
                    'select'=>array(
                        'title'=>'下拉框',
                        'class'=>"\\control\\fields_fbuilder\\Select"
                    ),
                    'decimal'=>array(
                        'title'=>'价格',
                        'class'=>"\\control\\fields_fbuilder\\Decimal"
                    ),
                    'integer'=>array(
                        'title'=>'数字',
                        'class'=>"\\control\\fields_fbuilder\\Integer"
                    ),
                    'radio'=>array(
                        'title'=>'单选框',
                        'class'=>"\\control\\fields_fbuilder\\Radio"
                    ),
                    'checkbox_list'=>array(
                        'title'=>'复选框',
                        'class'=>"\\control\\fields_fbuilder\\CheckboxList"
                    ),
                    'download'=>array(
                        'title'=>'文件下载',
                        'class'=>"\\control\\fields_fbuilder\\Download"
                    )
                )
            ),
            'supper'=>array(
                'title'=>'高级',
                'options'=>array(
                    'address'=>array(
                        'title'=>'国家/省/市/地区',
                        'class'=>"\\control\\fields_fbuilder\\Address"
                    ),
                    'date'=>array(
                        'title'=>'日期',
                        'class'=>"\\control\\fields_fbuilder\\Date"
                    ),
                    'datetime'=>array(
                        'title'=>'日期+时间',
                        'class'=>"\\control\\fields_fbuilder\\Datetime"
                    ),
                    'mobile'=>array(
                        'title'=>'手机',
                        'class'=>"\\control\\fields_fbuilder\\Mobile"
                    ),
                    'mobile_country_code'=>array(
                        'title'=>'手机国家号',
                        'class'=>"\\control\\fields_fbuilder\\MobileCountryCode"
                    ),
                    'email'=>array(
                        'title'=>'邮箱',
                        'class'=>"\\control\\fields_fbuilder\\Email"
                    ),
                    'file'=>array(
                        'title'=>'文件上传',
                        'class'=>"\\control\\fields_fbuilder\\File"
                    ),
                    'image'=>array(
                        'title'=>'图片上传',
                        'class'=>"\\control\\fields_fbuilder\\Image"
                    ),
                    'editor'=>array(
                        'title'=>'富文本',
                        'class'=>"\\control\\fields_fbuilder\\Editor"
                    ),
                    'html'=>array(
                        'title'=>'HTML文本',
                        'class'=>"\\control\\fields_fbuilder\\Html"
                    )
                )
            )
        );

        $results = array();
        foreach ($default as $groupKey=>$group){
            $options = array();
            foreach ($group['options'] as $key=>$field){
                if(in_array($key,$disabled_components)){
                    continue;
                }
                //不存在filter或filter白名单
                if(!count($allowed_components)||in_array($key,$allowed_components)){
                    $options[$key] = $field;
                }
            }

            if(count($options)){
                $group['options'] = $options;
                $results[$groupKey] =$group;
            }
        }
        return $results;
    }

    /**
     * 获取已开启的问券功能的字段列表
     *
     * @param array $fields 原始表单字段列表
     * @return array 已开启的问券功能的字段列表
     */
    public function getSurveyFields($fields){
        if(array_is_empty($fields)){
            return [];
        }

        $results = array();
        foreach ($fields as $fieldKey=>$field){
            if(isset($field['survey_enabled'])&&$field['survey_enabled']){
                $results[$fieldKey] = $field;
            }
        }

        return $results;
    }

    /**
     * 存储问券统计信息到数据库中
     *
     * @param string $objectType 数据类型，通常我们已数据库表来命名：如site_user  site_post
     * @param integer $objectId 当前entity（表单）数据的ID
     * @param array $fields 当前entity（表单）的字段列表
     * @param array $values 当前entity（表单）的值列表
     * @param integer $expo_user_id (可能废弃)当前展届下的用户ID
     * @throws Exception
     */
    public function saveSurvey($objectType,$objectId,$fields,$values,$expo_user_id){
    	if(!$fields||!is_array($fields)){
    		return;
    	}
    	
    	foreach ($fields as $fieldKey=>$field){
    		$this->handleSaveSurvey($objectType,$objectId,$fieldKey,$field,$values,$expo_user_id);
    	}
    }

    /**
     * 更新数据库中的问券统计信息
     *
     * @param string $objectType 数据类型，通常我们已数据库表来命名：如site_user  site_post
     * @param integer $objectId 当前entity（表单）数据的ID
     * @param array $fields 当前entity（表单）的字段列表
     * @param array $values 当前entity（表单）的值列表
     * @param integer $expo_user_id (可能废弃)当前展届下的用户ID
     * @throws Exception
     */
    public function updateSurvey($objectType,$objectId,$fields,$values,$expo_user_id){
        if(!$fields||!is_array($fields)){
            return;
        }

        foreach ($fields as $fieldKey=>$field){
            $this->handleClearSurvey($objectType,$objectId,$fieldKey,$field,$expo_user_id);
            $this->handleSaveSurvey($objectType,$objectId,$fieldKey,$field,$values,$expo_user_id);
        }
    }

    /**
     * 清除数据库中的问券统计信息到
     *
     * @param string $objectType 数据类型，通常我们已数据库表来命名：如site_user  site_post
     * @param integer $objectId 当前entity（表单）数据的ID
     * @param string $fieldKey 字段名
     * @param array $field 字段配置信息
     * @param integer $expo_user_id (可能废弃)当前展届下的用户ID
     * @throws Exception
     */
    public function handleClearSurvey($objectType,$objectId,$fieldKey,$field,$expo_user_id){
        if(empty($field['type'])){
            $field['type'] ="text";
        }
        if(!is_string($field['type'])&& is_callable($field['type'])){
            if(isset($field['type1'])){
                $field['type'] = $field['type1'];
            }else{
                throw new Exception(__('自定义字段，未设置type1属性'));
            }
        }
        $class = $this->getFieldClass($field['type']);
        if(!class_exists($class)){
            return;
        }

        $obj = new $class($fieldKey,$field);
        if(method_exists($obj,'handleClearSurvey')&&isset($field['survey_enabled'])&&$field['survey_enabled']){
            call_user_func_array(array($obj,'handleClearSurvey'),array($objectType,$objectId,$expo_user_id));
        }
    }

    /**
     * 更新数据库中的表单信息
     *
     * @param string $objectType 数据类型，通常我们已数据库表来命名：如site_user  site_post
     * @param integer $objectId 当前entity（表单）数据的ID
     * @param array $fields 当前entity（表单）的字段列表
     * @param array $values 当前entity（表单）的值列表
     * @throws Exception
     */
    public function updateData($objectType,$objectId,$fields,$values){
        $this->clearData($objectType,$objectId,$fields,$values);
        $this->saveData($objectType,$objectId,$fields,$values);
    }

    /**
     * 保存表单字段信息到数据库中
     *
     * @param string $objectType 数据类型，通常我们已数据库表来命名：如site_user  site_post
     * @param integer $objectId 当前entity（表单）数据的ID
     * @param array $fields 当前entity（表单）的字段列表
     * @param array $values 当前entity（表单）的值列表
     * @throws Exception
     */
    public function saveData($objectType,$objectId,$fields,$values){
        if(!$fields||!is_array($fields)){
            return;
        }
        $arrayKeys = array_keys($values);
        foreach ($fields as $fieldKey=>$field){
            if(!in_array($fieldKey,$arrayKeys)){
                continue;
            }
        	$this->handleSaveData($objectType,$objectId,$fieldKey,$field,$values);
        }

        do_action("app_form_data_{$objectType}_change",$objectId,$fields,$values);
        do_action("app_form_data_change",$objectType,$objectId,$fields,$values);
    }


    /**
     * 清除数据库中的表单信息
     *
     * @param string $objectType 数据类型，通常我们已数据库表来命名：如site_user  site_post
     * @param integer $objectId 当前entity（表单）数据的ID
     * @param array $fields 字段数值集
     * @param array $values
     * @throws Exception
     */
    public function clearData($objectType,$objectId,$fields,$values = []){
        if(!$fields||!is_array($fields)){
            return;
        }
        $arrayKeys = array_keys($values);
        foreach ($fields as $fieldKey=>$field){
            if(!in_array($fieldKey,$arrayKeys)){
                continue;
            }
            $this->handleClearData($objectType,$objectId,$fieldKey,$field);
        }
    }

    /**
     * 执行 存储问券统计信息到数据库中
     *
     * @param string $objectType 数据类型，通常我们已数据库表来命名：如site_user  site_post
     * @param integer $objectId 当前entity（表单）数据的ID
     * @param string $fieldKey 字段名
     * @param array $field 字段配置信息
     * @param array $values 当前entity（表单）的值列表
     * @param integer $expo_user_id (可能废弃)当前展届下的用户ID
     * @throws Exception
     */
    public function handleSaveSurvey($objectType,$objectId,$fieldKey,$field,$values,$expo_user_id){
        if(empty($field['type'])){
            $field['type'] ="text";
        }
        if(!is_string($field['type'])&& is_callable($field['type'])){
            if(isset($field['type1'])){
                $field['type'] = $field['type1'];
            }else{
                throw new Exception(__('自定义字段，未设置type1属性'));
            }
        }
        $class = $this->getFieldClass($field['type']);
        if(!class_exists($class)){
            return;
        }
        
        $obj = new $class($fieldKey,$field);
        if(method_exists($obj,'handleSaveSurvey')&&isset($field['survey_enabled'])&&$field['survey_enabled']){
        	call_user_func_array(array($obj,'handleSaveSurvey'),array($objectType,$objectId,$values,$expo_user_id));
        }
    }

    /**
     * 执行 存储表单信息信息到数据库中
     *
     * @param string $objectType 数据类型，通常我们已数据库表来命名：如site_user  site_post
     * @param integer $objectId 当前entity（表单）数据的ID
     * @param string $fieldKey 字段名
     * @param array $field 字段配置信息
     * @param array $values 当前entity（表单）的值列表
     * @throws Exception
     */
    public function handleSaveData($objectType,$objectId,$fieldKey,$field,$values){
    	if(empty($field['type'])){
    		$field['type'] ="text";
    	}

        if(!is_string($field['type'])&& is_callable($field['type'])){
           if(isset($field['type1'])){
               $field['type'] = $field['type1'];
           }else{
               throw new Exception(__('自定义字段，未设置type1属性'));
           }
        }
    	
    	$class = $this->getFieldClass($field['type']);
    	if(!class_exists($class)){
    		return;
    	}
    	$obj = new $class($fieldKey,$field);
    	if(method_exists($obj,'handleSaveData'))
    	call_user_func_array(array($obj,'handleSaveData'),array($objectType,$objectId,$values));
    }

    /**
     * 执行 清除表单信息信息到数据库中
     *
     * @param string $objectType 数据类型，通常我们已数据库表来命名：如site_user  site_post
     * @param integer $objectId 当前entity（表单）数据的ID
     * @param string $fieldKey 字段名
     * @param array $field 字段配置信息
     * @throws Exception
     */
    public function handleClearData($objectType,$objectId,$fieldKey,$field){
        if(empty($field['type'])){
            $field['type'] ="text";
        }
        if(!is_string($field['type'])&& is_callable($field['type'])){
            if(isset($field['type1'])){
                $field['type'] = $field['type1'];
            }else{
                throw new Exception(__('自定义字段，未设置type1属性'));
            }
        }
        $class = $this->getFieldClass($field['type']);
        if(!class_exists($class)){
            return;
        }
        $obj = new $class($fieldKey,$field);
        if(method_exists($obj,'handleClearData'))
        call_user_func_array(array($obj,'handleClearData'),array($objectType,$objectId));
    }
}