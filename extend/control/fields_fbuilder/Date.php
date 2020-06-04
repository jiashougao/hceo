<?php
namespace control\fields_fbuilder;

use think\db\Query;

class Date extends \control\fields\Date
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
            $joins["module_form_datetime form_{$this->key}"] = "form_{$this->key}.object_type='{$objectType}' and form_{$this->key}.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}.field_key='{$this->key}'";
            $where[]=[
                "form_{$this->key}.value",
                "eq",
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
            $joins["module_form_datetime form_{$this->key}"] = [
                "form_{$this->key}.object_type='{$objectType}' and form_{$this->key}.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}.field_key='{$this->key}'",
                'left'
            ];
            $sorts[$this->key]= "form_{$this->key}_value";
            $groupField[$this->key] = "max(form_{$this->key}.value) as form_{$this->key}_value";
        }
    }

	public function handleClearData($objectType,$objectId){
		db_default("module_form_datetime")->where(array(
				'object_type'=>$objectType,
				'object_id'=>$objectId,
				'field_key'=>$this->key
		))->delete();
	}
	public function handleSaveData($objectType,$objectId,$values){
		$value = isset($values[$this->key])?$values[$this->key]:null;
		db_default("module_form_datetime")->insert(array(
				'object_type'=>$objectType,
				'object_id'=>$objectId,
				'field_key'=>$this->key,
				'value'=>$value?$value:null,
                'value_md5'=>$value?md5($value):null
		));
		return $value;
	}
	
    public static function generateScript(){
        ?>
        <script type="text/javascript">
            (function($,editor){
                editor.generate_date_field = function(fieldKey,data,cel){
                    data = $.extend({
                        'title':'未命名',
                        'description':'',
                        'readonly':false,
                        'required':false,
                        'placeholder':'',
                        'style':''
                    },data);
                    return '<div data-field="'+fieldKey+'" data-type="date" class="form-editor-field fui-form-builder--col fui-form-builder--col-'+(12/cel)+' ui-draggable ui-draggable-handle" >' +
                        '<div class="fui-form-field forminator-field-type_name">'+
                        '<label class="sui-label">'+data.title+' <b style="color:green;">'+fieldKey+'</b> <span class="required">'+(data.required?'*':'')+'</span></label>'+
                        '<input type="text" class="sui-form-control" style="'+data.style+'" placeholder="'+data.placeholder+'" />'+
                        '<span class="sui-description">'+data.description+'</span>'+
                        '</div>'+
                        '</div>';
                };

                editor.get_date_config=function(fieldKey,data){
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
                                'placeholder':{
                                    'title': 'Placeholder',
                                    'type':'textarea'
                                },
                                'description':{
                                    'title': '字段描述',
                                    'type':'textarea'
                                },
                                'required': {
                                    'title': '内容必填',
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
                                'readonly': {
                                    'title': '内容只读，不可修改',
                                    'type':'checkbox'
                                },
                                'validate':{
                                    'title': '内容验证器',
                                    'placeholder':'对字段进行复杂的验证',
                                    'description':'此字段由开发人员提供，请勿随意填写',
                                    'type':'textarea'
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
            <label class="control-label">
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