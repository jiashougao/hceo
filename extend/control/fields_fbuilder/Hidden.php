<?php
namespace control\fields_fbuilder;

class Hidden extends \control\fields\Hidden
{
	public function handleClearData($objectType,$objectId){
		db_default("module_form_varchar")->where(array(
				'object_type'=>$objectType,
				'object_id'=>$objectId,
				'field_key'=>$this->key
		))->delete();
	}
	public function handleSaveData($objectType,$objectId,$values){
		$value = isset($values[$this->key])?$values[$this->key]:null;
		db_default("module_form_varchar")->insert(array(
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
                editor.generate_hidden_field = function(fieldKey,data,cel){
                    data = $.extend({
                        'title':'未命名',
                        'description':'',
                        'required':false,
                        'placeholder':'',
                        'disabled':false,
                        'validate':'',
                        'style':''
                    },data);
                    return '<div data-field="'+fieldKey+'" data-type="hidden" class="form-editor-field fui-form-builder--col fui-form-builder--col-'+(12/cel)+' ui-draggable ui-draggable-handle" >' +
                        '<div class="fui-form-field forminator-field-type_name">'+
                        '<label class="sui-label">'+data.title+' <b style="color:green;">'+fieldKey+'</b> <span class="required">'+(data.required?'*':'')+'</span></label>'+
                        '[Hidden Field]'+
                        '<span class="sui-description">'+data.description+'</span>'+
                        '</div>'+
                        '</div>';
                };

                editor.get_hidden_config=function(fieldKey,data){
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
}