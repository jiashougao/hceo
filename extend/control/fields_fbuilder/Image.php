<?php
namespace control\fields_fbuilder;

class Image extends \control\fields\Image
{

    /**
     * 对数据查询进行排序
     *  * @param array $joins join
     * @param string $primaryTable 主表
     * @param string $objectType 数据标识
     * @param array $sorts 排序
     */
    public function sort(&$joins,&$where,&$sorts,&$groupField,$primaryTable,$primaryIdName,$objectType){
        if($this->get('sortable')){
            $joins["module_form_text form_{$this->key}"] =[
                "form_{$this->key}.object_type='{$objectType}' and form_{$this->key}.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}.field_key='{$this->key}'",
                'left'
            ];
            $sorts[$this->key]= "form_{$this->key}_value";
            $groupField[$this->key] = "max(form_{$this->key}.value_md5) as form_{$this->key}_value";
        }
    }


    public function handleClearData($objectType,$objectId){
        db_default("module_form_text")->where(array(
            'object_type'=>$objectType,
            'object_id'=>$objectId,
            'field_key'=>$this->key
        ))->delete();
    }

    public function handleSaveData($objectType,$objectId,$values){
        $valueList = isset($values[$this->key])?maybe_json_decode($values[$this->key],true):null;
        $dataList = array();
        if($valueList&&is_array($valueList)){
            foreach ($valueList as $value){
                $dataList[]=array(
                    'object_type'=>$objectType,
                    'object_id'=>$objectId,
                    'field_key'=>$this->key,
                    'value'=>maybe_json_encode($value),
                    'value_md5'=>$value&&is_array($value)&&!empty($value['url'])?md5(maybe_json_encode($value)):null
                );
            }
        }

        if(count($dataList)){
            db_default("module_form_text")->insertAll($dataList);
        }
        
        return $dataList;
    }

    public static function generateScript(){
        ?>
        <script type="text/javascript">
            (function($,editor){
                editor.generate_image_field = function(fieldKey,data,cel){
                    data = $.extend({
                        'title':'未命名',
                        'description':'',
                        'max':1,
                        'disabled':false,
                        'required':false,
                        'placeholder':''
                    },data);
                    let html =  '<div data-field="'+fieldKey+'" data-type="image" class="form-editor-field fui-form-builder--col fui-form-builder--col-'+(12/cel)+' ui-draggable ui-draggable-handle" >' +
                        '<div class="fui-form-field forminator-field-type_name">'+
                                '<label class="sui-label">'+data.title+' <b style="color:green;">'+fieldKey+'</b> <span class="required">'+(data.required?'*':'')+'</span></label>';
                                html+='<div style="margin:10px 10px;"><button type="button" class="btn btn-primary">选择图片</button></div>';
                                html+='</div>';

                    html+= '</div>'+
                        '</div>';
                    return html;
                };

                editor.get_image_config=function(fieldKey,data){
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
                                'max':{
                                    'title': '数量限制',
                                    'type':'text',
                                    'default':1
                                },
                                'required': {
                                    'title': '选项必选',
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
                                'disabled': {
                                    'title': '禁用',
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
            <div>
                <?php parent::generateField($form_id)?>
            </div>
            <span class="help-block"><?php echo $data['description'] ?></span>
        </div>
        <?php
    }
}