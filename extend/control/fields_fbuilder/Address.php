<?php
namespace control\fields_fbuilder;

use think\db\Query;

class Address extends \control\fields\Address
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
        $value = maybe_json_decode($value);
        $country = !empty($value['country'])?$value['country']:'';
        $province = !empty($value['province'])?$value['province']:'';
        $city= !empty($value['city'])?$value['city']:'';
        $district = !empty($value['district'])?$value['district']:'';
       // $street = !empty($value['street'])?$value['street']:'';

        if($this->get('searchable')){
            if($country){
                $joins["module_form_varchar form_{$this->key}_country"] = "form_{$this->key}_country.object_type='{$objectType}' and form_{$this->key}_country.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}_country.field_key='{$this->key}' and form_{$this->key}_country.field_key1='country'";
                $where[]=[
                    "form_{$this->key}_country.value",
                    "eq",
                    $country
                ];
            }

            if($province){
                $joins["module_form_varchar form_{$this->key}_province"] = "form_{$this->key}_province.object_type='{$objectType}' and form_{$this->key}_province.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}_province.field_key='{$this->key}' and form_{$this->key}_province.field_key1='province'";
                $where[]=[
                    "form_{$this->key}_province.value",
                    "eq",
                    $province
                ];
            }

            if($city){
                $joins["module_form_varchar form_{$this->key}_city"] = "form_{$this->key}_city.object_type='{$objectType}' and form_{$this->key}_city.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}_city.field_key='{$this->key}' and form_{$this->key}_city.field_key1='city'";
                $where[]=[
                    "form_{$this->key}_city.value",
                    "eq",
                    $city
                ];
            }

            if($district){
                $joins["module_form_varchar form_{$this->key}_district"] = "form_{$this->key}_district.object_type='{$objectType}' and form_{$this->key}_district.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}_district.field_key='{$this->key}' and form_{$this->key}_district.field_key1='district'";
                $where[]=[
                    "form_{$this->key}_district.value",
                    "eq",
                    $district
                ];
            }
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
            $joins["module_form_varchar form_{$this->key}_province"] =[
                "form_{$this->key}_province.object_type='{$objectType}' and form_{$this->key}_province.object_id={$primaryTable}.{$primaryIdName} and form_{$this->key}_province.field_key='{$this->key}' and form_{$this->key}_province.field_key1='province'",
                'left'
            ];
            $sorts[$this->key]= "form_{$this->key}_province_value";
            $groupField[$this->key] = "max(form_{$this->key}_province.value) as form_{$this->key}_province_value";
        }
    }


    public function handleClearData($objectType,$objectId){
        db_default('module_form_varchar')->where(array(
            'object_type'=>$objectType,
            'object_id'=>$objectId,
            'field_key'=>$this->key
        ))->delete();
    }

    public function handleSaveData($objectType,$objectId,$values){
        $value = maybe_json_decode(isset($values[$this->key])?$values[$this->key]:[]);
        if(array_is_empty($value)){$value=[];}

        $country = !empty($value['country'])?$value['country']:'';
        $province = !empty($value['province'])?$value['province']:'';
        $city= !empty($value['city'])?$value['city']:'';
        $district = !empty($value['district'])?$value['district']:'';
        $street = !empty($value['street'])?$value['street']:'';

        db_default('module_form_varchar')->insert(array(
            'object_type'=>$objectType,
            'object_id'=>$objectId,
            'field_key'=>$this->key,
            'field_key1'=>'country',
            'value'=>$country,
            'value_md5'=>$country?md5($country):null
        ));
        db_default('module_form_varchar')->insert(array(
            'object_type'=>$objectType,
            'object_id'=>$objectId,
            'field_key'=>$this->key,
            'field_key1'=>'province',
            'value'=>$province,
            'value_md5'=>$province?md5($province):null
        ));
        db_default('module_form_varchar')->insert(array(
            'object_type'=>$objectType,
            'object_id'=>$objectId,
            'field_key'=>$this->key,
            'field_key1'=>'city',
            'value'=>$city,
            'value_md5'=>$city?md5($city):null
        ));
        db_default('module_form_varchar')->insert(array(
            'object_type'=>$objectType,
            'object_id'=>$objectId,
            'field_key'=>$this->key,
            'field_key1'=>'district',
            'value'=>$district,
            'value_md5'=>$district?md5($district):null
        ));
        db_default("module_form_text")->insert(array(
            'object_type'=>$objectType,
            'object_id'=>$objectId,
            'field_key'=>$this->key,
            'field_key1'=>'street',
            'value'=>$street,
            'value_md5'=>$street?md5($street):null
        ));
        return $value;
    }

    public static function generateScript(){
        ?>
        <script type="text/javascript">
            (function($,editor){
                editor.generate_address_field = function(fieldKey,data,cel){
                    data = $.extend({
                        'title':'未命名',
                        'description':'',
                        'country':true,
                        'province_city_district':true,
                        'street':true,
                        'readonly':false,
                        'required':false
                    },data);
                    return '<div data-field="'+fieldKey+'" data-type="address" class="form-editor-field fui-form-builder--col fui-form-builder--col-'+(12/cel)+' ui-draggable ui-draggable-handle" >' +
                                '<div class="fui-form-field forminator-field-type_name">'+
                                    '<label class="sui-label">'+data.title+' <b style="color:green;">'+fieldKey+'</b> <span class="required">'+(data.required?'*':'')+'</span></label>'+
                                    '<div class="'+(data.country?'':'hide')+'"><select class="form-control"><option>中国</option></select></div>'+
                                    '<div class="d-flex flex-row '+(data.province_city_district?'':'hide')+'" style="margin-top:5px;">' +
                                        '<select class="form-control" style="margin-right:5px;"><option>北京</option></select><select class="form-control" style="margin-right:5px;"><option>北京市</option></select><select class="form-control" ><option>朝阳区</option></select>'+
                                    '</div>'+
                                    '<textarea style="margin-top:5px;" rows="3" class="sui-form-control  '+(data.street?'':'hide')+'" placeholder="详细街道地址"></textarea>'+
                                    '<span class="sui-description">'+data.description+'</span>'+
                                '</div>'+
                            '</div>';
                };

                editor.get_address_config=function(fieldKey,data){
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
                                'country':{
                                    'title': '显示国家选择项',
                                    'type':'checkbox',
                                    'default':true
                                },
                                'province_city_district':{
                                    'title': '显示省/市/区选择项',
                                    'type':'checkbox',
                                    'default':true
                                },
                                'street':{
                                    'title': '显示街道地址文本框',
                                    'type':'checkbox',
                                    'default':true
                                },
                                'description':{
                                    'title': '字段描述',
                                    'type':'textarea'
                                },
                                'empty_option':{
                                    'title': '默认选项留空',
                                    'type':'checkbox',
                                    'default':false
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
            <div>
                <?php parent::generateField($form_id)?>
                <span class="help-block"><?php echo $data['description'] ?></span>
            </div>
        </div>
        <?php
    }
}