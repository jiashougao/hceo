<?php
namespace control\fields_fbuilder;

use think\db\Query;

class Checkbox extends \control\fields\Checkbox
{
    /**
     * 对数据查询进行过滤
     *
     * @param array $where where查询条件
     * @param array $joins join
     * @param string $primaryTable 主表
     * @param string $objectType 数据标识
     * @param mixed $value 查询值
     */
    public function where(&$joins,&$where,$primaryTable,$primaryIdName,$objectType,$value){
        if(!$value){
            return;
        }

        if($this->get('searchable')){
            $joins["module_form_varchar form_{$this->key}"] = "form_{$this->key}.object_type='{$objectType}' and form_{$this->key}.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}.field_key='{$this->key}'";
            $where[]=[
                "form_{$this->key}.value",
                'eq',
                $value
            ];
        }
    }

    /**
     * 对数据查询进行排序
     *  * @param array $joins join
     * @param string $primaryTable 主表
     * @param string $objectType 数据标识
     * @param array $sorts 排序
     */
    public function sort(&$joins,&$where,&$sorts,&$groupField,$primaryTable,$primaryIdName,$objectType){
        if($this->get('sortable')){
            $joins["module_form_varchar form_{$this->key}"] =[
                "form_{$this->key}.object_type='{$objectType}' and form_{$this->key}.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}.field_key='{$this->key}'",
                'left'
            ];
            $sorts[$this->key]= "form_{$this->key}_value";
            $groupField[$this->key] = "max(form_{$this->key}.value) as form_{$this->key}_value";
        }
    }

    public function handleClearData($objectType,$objectId){
        db_default('module_form_varchar')->where(array(
            'object_type'=>$objectType,
            'object_id'=>$objectId,
            'field_key'=>$this->key
        ))->delete();
    }
    public function handleClearSurvey($objectType,$objectId,$expo_user_id){
        db_default('module_form_survey')->where(array(
            'expo_id'=>request()->current_expo['id'],
            'expo_user_id'=>$expo_user_id,
            'object_id'=>$objectId,
            'object_type'=>$objectType,
            'field_key'=>$this->key
        ))->delete();
    }
    public function handleSaveSurvey($objectType,$objectId,$values,$expo_user_id){
	    	$value = isset($values[$this->key])?$values[$this->key]:null;
        if(!is_string($this->data['options'])&&is_callable($this->data['options'])){
            $this->data['options'] = call_user_func($this->data["options"]);
        }

	    	$data = array(
	    			'expo_id'=>request()->current_expo['id'],
	    			'expo_user_id'=>$expo_user_id,
	    			'object_id'=>$objectId,
	    			'object_type'=>$objectType,
                    'value_md5'=>$value?md5($value):null,
                    'value'=>$value,
                    'is_option'=>array_is( $this->data['options'])&& isset( $this->data['options'][$value])?1:0,
	    			'field_key'=>$this->key,
	    			'created_time'=>strtotime(date('Y-m-d H:i:s'))
	    	);
	    	db_default('module_form_survey')->insert($data);
    }
    
    public function handleSaveData($objectType,$objectId,$values){
        $value = isset($values[$this->key])?$values[$this->key]:null;
        db_default('module_form_varchar')->insert(array(
            'object_type'=>$objectType,
            'object_id'=>$objectId,
            'field_key'=>$this->key,
            'value'=>$value,
            'value_md5'=>$value?md5($value):null
        ));
        
        return $value;
    }

    public static function generateScript(){
        ?>
        <script type="text/javascript">
            (function($,editor){
                editor.generate_checkbox_field = function(fieldKey,data,cel){
                    data = $.extend({
                        'title':'未命名',
                        'description':'',
                        'required':false,
                        'placeholder':'',
                        'disabled':false,
                        'validate':'',
                        'style':''
                    },data);
                    return '<div data-field="'+fieldKey+'" data-type="checkbox" class="form-editor-field fui-form-builder--col fui-form-builder--col-'+(12/cel)+' ui-draggable ui-draggable-handle" >' +
                                '<div class="fui-form-field forminator-field-type_name">'+
                                    '<label class="sui-label">'+data.title+' <b style="color:green;">'+fieldKey+'</b> <span class="required">'+(data.required?'*':'')+'</span></label>'+
                                    '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">'+
                                        '<input type="checkbox" name="model-id" class="checkboxes" value="5">'+
                                        '<span></span>'+
                                    '</label>'+
                                    '<span class="sui-description">'+data.description+'</span>'+
                                '</div>'+
                            '</div>';
                };

                editor.get_checkbox_config=function(fieldKey,data){
                    return {
                        'base':{
                            'title':'标准',
                            'options': {
                                'title': {
                                    'title': '字段标题',
                                    'required':true,
                                    'type':'text'
                                },
                                'name':{
                                    'title': '字段名(字母/数字/下划线组合)',
                                    'required':true,
                                    'readonly':typeof data.columnNameFixed!=='undefined'&&data.columnNameFixed,
                                    'type':'text',
                                    'description':'<span style="color:green;">提示：简单的英文名便于后期数据的统计</span>'
                                },
                                'group':{
                                    'title': '字段所属组',
                                    'type':'text',
                                    'placeholder':'字段在编辑模式下的TAB组'
                                },
                                'description':{
                                    'title': '字段描述',
                                    'type':'textarea'
                                },
                                'required': {
                                    'title': '选项必须勾选',
                                    'type':'checkbox'
                                }
                            }
                        },
                        'senior':{
                            'title':'高级',
                            'options': {
                                'sort':{
                                    'title': '字段排序',
                                    'type':'text',
                                    'placeholder':'值越大越靠后'
                                },
                                'hide_in_edit':{
                                    'title': '编辑页面隐藏',
                                    'type':'checkbox',
                                    'default':false
                                },
                                'hide_in_list':{
                                    'title': '列表页面隐藏',
                                    'type':'checkbox',
                                    'default':false
                                },
                                'sortable':{
                                    'title': '开启排序',
                                    'type':'checkbox',
                                    'default':false
                                },
                                'searchable':{
                                    'title': '开启查询',
                                    'type':'checkbox',
                                    'default':false
                                },
                                'disabled': {
                                    'title': '禁止变动选中项',
                                    'type':'checkbox'
                                }
                            }
                        },
                        'survey':{
                            'title':'问券',
                            'options': {
                                'survey_enabled': {
                                    'title': '启用问券统计',
                                    'type':'checkbox'
                                }
                            }
                        }
                    };
                }
            })(jQuery,formBuilder);
        </script>
        <?php
    }

    public function generateField($form_id) {
        $defaults = array (
            'title' => '',
            'required'=>false,
            'description' => ''
        );

        $data = parse_args ( $this->data, $defaults );

        ?>
        <div class="form-group <?php echo esc_attr($this->key)?>">
            <label>
                <?php echo $data['title'];?>
                <?php if($data['required']){
                    ?>
                    <span class="required"> * </span>
                    <?php
                }?>
            </label>
            <?php parent::generateField($form_id)?>
            <span class="help-block"><?php echo $data['description'] ?></span>
        </div>
        <?php
    }
}