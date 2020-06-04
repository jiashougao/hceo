<?php
namespace control\fields_fbuilder;

class Html extends \control\fields\Html
{
    public function handleClearData($objectType,$objectId){

    }
    public function handleSaveData($objectType,$objectId,$values){
		return null;
    }
    public function generateField($form_id) {
        ?><div class="form-group">
            <?php echo $this->data['description']; ?>
        </div><?php
    }
    public static function generateScript(){
        ?>
        <script type="text/javascript">
            (function($,editor){
                editor.generate_html_field = function(fieldKey,data,cel){
                    data = $.extend({
                        'description':''
                    },data);
                    return '<div data-field="'+fieldKey+'" data-type="html" class="form-editor-field fui-form-builder--col fui-form-builder--col-'+(12/cel)+' ui-draggable ui-draggable-handle" >' +
                                '<div class="fui-form-field forminator-field-type_name">'+
                                    data.description+
                                '</div>'+
                            '</div>';
                };

                editor.get_html_config=function(fieldKey,data){
                    return {
                        'base':{
                            'title':'标准',
                            'options': {
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
                                    'type':'editor'
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