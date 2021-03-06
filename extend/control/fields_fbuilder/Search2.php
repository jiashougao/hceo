<?php
namespace control\fields_fbuilder;

class Search2 extends \control\fields\Search2
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
        $value = $value&&is_numeric($value)?[$value]: maybe_json_decode($value);
        if($value&&!is_array($value)){
            //兼容老数据
            $value = [$value];
        }

        if(!$value||!count($value)){
            return;
        }

        if($this->get('searchable')){
            $joins["module_form_varchar form_{$this->key}"] = "form_{$this->key}.object_type='{$objectType}' and form_{$this->key}.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}.field_key='{$this->key}'";
            $where[]=[
                "form_{$this->key}.value",
                "in",
                $value
            ];
        }
    }

    /**
     * 对数据查询进行排序
     *  *
     * @param array $joins join
     * @param $where
     * @param array $sorts 排序
     * @param $groupField
     * @param string $primaryTable 主表
     * @param string $objectType 数据标识
     */
    public function sort(&$joins,&$where,&$sorts,&$groupField,$primaryTable,$primaryIdName,$objectType){
        if($this->get('sortable')){
            self::initFilter();
            list($joins,$where,$sorts,$groupField) = apply_filters("app_search2_{$this->get('action')}_sort",[$joins,$where,$sorts,$groupField],$primaryTable,$primaryIdName,$objectType,$this);
        }
    }

    public function handleClearData($objectType,$objectId){
        db_default("module_form_varchar")->where(array(
            'object_type'=>$objectType,
            'object_id'=>$objectId,
            'field_key'=>$this->key
        ))->delete();
    }
    public function handleSaveData($objectType,$objectId,$values){
        $value = isset($values[$this->key])?$values[$this->key]:null;
        $value = $value&&is_numeric($value)?[$value]: maybe_json_decode($value);
        if($value&&!is_array($value)){
            //兼容老数据
            $value = [$value];
        }

        foreach ($value as $val){
            db_default("module_form_varchar")->insert(array(
                'object_type'=>$objectType,
                'object_id'=>$objectId,
                'field_key'=>$this->key,
                'value'=>$val
            ));
        }

        return $value;
    }

    public static function generateScript(){
        ?>
        <script type="text/javascript">
            (function($,editor){
                editor.generate_search2_field = function(fieldKey,data,cel){
                    data = $.extend({
                        'title':'未命名',
                        'description':'',
                        'required':false,
                        'readonly':false,
                        'placeholder':'',
                        'style':''
                    },data);
                    return '<div data-field="'+fieldKey+'" data-type="search2" class="form-editor-field fui-form-builder--col fui-form-builder--col-'+(12/cel)+' ui-draggable ui-draggable-handle" >' +
                                '<div class="fui-form-field forminator-field-type_name">'+
                                    '<label class="sui-label">'+data.title+' <b style="color:green;">'+fieldKey+'</b> <span class="required">'+(data.required?'*':'')+'</span></label>'+
                                    '<select class="sui-form-control" ><option>查询...</option></select>'+
                                    '<span class="sui-description">'+data.description+'</span>'+
                                '</div>'+
                            '</div>';
                };

                editor.get_search2_config=function(fieldKey,data){
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
                                'action':{
                                    'title': '数据Action',
                                    'type':'text'
                                },
                                'multi':{
                                    'title': '是否多选',
                                    'type':'select',
                                    'options':{
                                        0:'否',
                                        1:'是'
                                    }
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