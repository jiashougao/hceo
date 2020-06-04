<?php
namespace control\fields;

use think\Exception;
use think\facade\Env;

/**
 * 地址字段
 * Class Address
 * @package control\fields
 */
class Address extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'readonly' => false,
        'class' => array(),
        'style' => array(
            'width'=>'150px',
            'margin-right'=>'5px'
        ),
        /**
         * 标识是否默认不选中
         */
        'empty_option' => true,
        'placeholder' => '',
        //标记是否显示国家
        'country'=>true,
        //标记是否显示省市区
        'province_city_district'=>true,
        //标记是否显示详细街道地址
        'street'=>true,

        'default'=>array(
            'country'=>'',
            'province'=>'',
            'city'=>'',
            'district'=>'',
            'street'=>''
        ),
        'value'=>'',
        'custom_attributes' => array (),
        'options'=>array(),

        /**
         * excel导入数据=>系统数据
         * @var callable
         */
        'excelToData'=>__CLASS__.'::excelToData'
    );

    /**
     * @param $res
     * @param null $call
     * @return mixed
     * @throws Exception
     */
    public function param(&$res,$call=null){
        $addressValue = parent::param($res,$call);
        if(isset($this->data['required'])&&$this->data['required']){
            $value = maybe_json_decode($addressValue,true);
            $country = !empty($value['country'])?$value['country']:'';
            $province = !empty($value['province'])?$value['province']:'';
            $city= !empty($value['city'])?$value['city']:'';
            $district = !empty($value['district'])?$value['district']:'';
            $street = !empty($value['street'])?$value['street']:'';

            if(get_lang()=='zh-cn'){

                if(empty($country)){
                    throw new Exception(errorMessage(10000,array(__('国家'))),10000);
                }

                if($country=='中国'){
                    if(empty($province)){
                        throw new Exception(errorMessage(10000,array(__('省份'))),10000);
                    }

                    if(empty($city)){
                        throw new Exception(errorMessage(10000,array(__('城市'))),10000);
                    }

                    if(empty($district)){
                        throw new Exception(errorMessage(10000,array(__('地区'))),10000);
                    }
                }

               // if($this->get('street')&& empty($street)){
                   // throw new Exception(errorMessage(10000,array(__('街道地址'))),10000);
               // }
            }else{
                if(empty($country)){
                    throw new Exception(errorMessage(10000,array(__('国家'))),10000);
                }

//                if($this->get('street')&& empty($street)){
//                    throw new Exception(errorMessage(10000,array(__('街道地址'))),10000);
//                }
            }
        }

        return $addressValue;
    }

    /**
     * @param mixed $excelOrRealData 字符串或数组
     * @return mixed
     */
    public static function excelToData($excelOrRealData){
        $data = maybe_json_decode($excelOrRealData);
        if(!array_is_empty($data)){
            return $data;
        }

        $data = explode(";",$excelOrRealData);
        $dataArray = array(
            'country' => "",
            'province' => "",
            'city' => "",
            'district' => "",
            'street' => "",
        );
        if(array_is_empty($data)){
            return $dataArray;
        }
        foreach ($data as $key => $value){
            $value = preg_replace('/\s+/', '', $value);
            if( $key == 0){
                $dataArray['country'] = $value;
            }
            if( $key == 1){
                $dataArray['province'] = $value;
            }
            if( $key == 2){
                $dataArray['city'] = $value;
            }
            if( $key == 3){
                $dataArray['district'] = $value;
            }
            if( $key == 4){
                $dataArray['street'] = $value;
            }
        }
        return $dataArray;
    }

    public function getValue($format=false)
    {
        if(!$format){
            return parent::getValue();
        }
        return maybe_json_decode(parent::getValue());
    }

    public function preview($strip_tags = false,$values=null){
        $value = maybe_json_decode($this->getValue());
        if(array_is_empty($value)){$value=[];}
        $value = short_args($value,$this->data['default']);
        $country = !empty($value['country'])?$value['country']:'';
        $province = !empty($value['province'])?$value['province']:'';
        $city= !empty($value['city'])?$value['city']:'';
        $district = !empty($value['district'])?$value['district']:'';
        $street = !empty($value['street'])?$value['street']:'';

        $value = trim("{$country} {$province},{$city},{$district} {$street}");
        return $strip_tags?$value:"<div class='form-control-textarea-preview'>{$value}</div>";
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );

        $value = $this->getValue(true);

        $country = $value&&!empty($value['country'])?$value['country']:'';
        $province = $value&&!empty($value['province'])?$value['province']:'';
        $city= $value&&!empty($value['city'])?$value['city']:'';
        $district =$value&& !empty($value['district'])?$value['district']:'';
        $street = $value&&!empty($value['street'])?$value['street']:'';

        if(get_lang()=='zh-cn') {
            $countryList = db_default('module_country')->select();
            ?>
            <div class="d-flex flex-column align-items-start">
                <div id="<?php echo $field ?>-fieldset-country" class="d-flex d-flex-row hide"
                     style="margin-bottom: 5px;">
                    <select id="<?php echo $field ?>-country"
                            class="form-control <?php echo esc_attr(join(' ', array_unique($this->data['class']))); ?>"
                            name="<?php echo $field ?>-country"
                            style="<?php echo $this->getCustomerStyleHtml(); ?>" <?php echo $this->data['disabled'] ? 'disabled' : ''; ?> <?php echo $this->data['readonly'] ? 'readonly' : ''; ?>   <?php echo $this->getCustomAttributeHtml(); ?> >
                        <?php
                        if ($this->data['empty_option'] || $this->get('searching')) {
                            ?>
                            <option value="">—— <?php echo __('国家') ?> ——</option> <?php
                        }
                        if ($countryList) {
                            foreach ($countryList as $countryItem) {
                                ?>
                                <option <?php echo $country == $countryItem['name_CN'] ? 'selected' : ''; ?>
                                value="<?php echo esc_attr($countryItem['name_CN']) ?>"><?php echo $countryItem['name_CN'] ?></option><?php
                            }
                        } ?>
                    </select>
                </div>

                <div id="<?php echo $field ?>-fieldset-address" <?php echo $this->data['empty_option'] || $this->get('searching') ? ' data-placeholder="true"' : '' ?>
                     data-toggle="distpicker" class="hide">
                    <select id="<?php echo $field ?>-province" data-province="<?php echo esc_attr($province) ?>"
                            class="<?php echo !array_key_exists('province', $this->data['default']) ? 'hide' : ''; ?> form-control <?php echo esc_attr(join(' ', array_unique($this->data['class']))); ?>"
                            name="<?php echo $field ?>-province"
                            style="<?php echo $this->getCustomerStyleHtml(); ?>" <?php echo $this->data['disabled'] ? 'disabled' : ''; ?> <?php echo $this->data['readonly'] ? 'readonly' : ''; ?>   <?php echo $this->getCustomAttributeHtml(); ?> >

                    </select>
                    <div class="d-flex flex-row ">

                        <select id="<?php echo $field ?>-city" data-city="<?php echo esc_attr($city) ?>"
                                class="<?php echo !array_key_exists('city', $this->data['default']) ? 'hide' : ''; ?> form-control <?php echo esc_attr(join(' ', array_unique($this->data['class']))); ?>"
                                name="<?php echo $field ?>-city"
                                style="<?php echo $this->getCustomerStyleHtml(); ?>" <?php echo $this->data['disabled'] ? 'disabled' : ''; ?> <?php echo $this->data['readonly'] ? 'readonly' : ''; ?>   <?php echo $this->getCustomAttributeHtml(); ?> >

                        </select>
                        <select id="<?php echo $field ?>-district" data-district="<?php echo esc_attr($district) ?>"
                                class="<?php echo !array_key_exists('district', $this->data['default']) ? 'hide' : ''; ?> form-control <?php echo esc_attr(join(' ', array_unique($this->data['class']))); ?>"
                                name="<?php echo $field ?>-district"
                                style="<?php echo $this->getCustomerStyleHtml(); ?>" <?php echo $this->data['disabled'] ? 'disabled' : ''; ?> <?php echo $this->data['readonly'] ? 'readonly' : ''; ?>   <?php echo $this->getCustomAttributeHtml(); ?> >

                        </select>
                    </div>
                </div>
                <div id="<?php echo $field ?>-fieldset-street"
                     class="d-flex d-flex-row <?php echo !$this->data['street'] || $this->get('searching') ? 'hide' : ''; ?>"
                     style="margin-top: 5px;width: 100%;padding: 10px 0px;">
                    <textarea id="<?php echo $field ?>-street" rows="3" placeholder="<?php echo __('详细街道地址') ?>"
                              class="form-control <?php echo esc_attr(join(' ', array_unique($this->data['class']))); ?>"
                              name="<?php echo $field ?>-street" <?php echo $this->data['disabled'] ? 'disabled' : ''; ?> <?php echo $this->data['readonly'] ? 'readonly' : ''; ?> <?php echo $this->getCustomAttributeHtml(); ?> ><?php echo esc_textarea($street) ?></textarea>
                </div>
            </div>
            <script type="text/javascript">
                (function ($, undefined) {
                    $(function () {
                        $("#<?php echo $field?>-fieldset-address").distpicker({
                            placeholder:<?php echo $this->data['empty_option'] || $this->get('searching') ? 'true' : 'false'?>
                        });


                        <?php if($this->data['country'] ){
                        ?>$('#<?php echo $field?>-fieldset-country').removeClass('hide');
                        <?php
                        }else{
                        if(!($this->data['empty_option'] || $this->get('searching'))){
                        ?>$('#<?php echo $field?>-country').val('中国');<?php
                        }

                        }?>
                        let province_city_district = "<?php echo $this->data['province_city_district'] ? 'yes' : 'no';?>";
                        $('#<?php echo $field?>-country').change(function () {
                            let country = $(this).val();
                            if (country === '中国' && province_city_district === 'yes') {
                                $('#<?php echo $field?>-fieldset-address').removeClass('hide');
                            } else {
                                $('#<?php echo $field?>-fieldset-address').addClass('hide');
                            }
                        }).change();

                        $(document).bind("handle_<?php echo $form_id?>_reset", function (e, form) {
                            $('#<?php echo $field?>-country').change();
                            $("#<?php echo $field?>-fieldset-address").distpicker('reset');
                            $('#<?php echo $field?>-province').change();
                            $('#<?php echo $field?>-city').change();
                        });

                        let getAddressValue = function () {
                            let result = {
                                country: '中国'
                            };
                            <?php if($this->data['country']){
                                ?>result.country = $('#<?php echo $field?>-country').val();<?php
                            }?>

                            <?php if($this->data['province_city_district']){
                            ?>
                            if (result.country === '中国') {
                                result.province = $('#<?php echo $field?>-province').val();
                                result.city = $('#<?php echo $field?>-city').val();
                                result.district = $('#<?php echo $field?>-district').val();
                            }
                            <?php
                            }?>

                            <?php if($this->data['street']){
                                ?>result.street = $('#<?php echo $field?>-street').val();<?php
                            }?>
                            return result;
                        };
                        $('#<?php echo $field?>-province,#<?php echo $field?>-city,#<?php echo $field?>-dist').change(function () {
                            $(document).trigger("handle_<?php echo $form_id?>_column_change", {
                                column: "<?php echo $field?>",
                                event: 'keyup',
                                value: JSON.stringify(getAddressValue())
                            });
                        });

                        $(document).bind("handle_<?php echo $form_id?>_submit", function (e, form) {
                            form.<?php echo esc_attr($this->key)?> = JSON.stringify(getAddressValue());
                        });

                        window.set_field_<?php echo $field?>_value = function (value) {
                            value = $.extend({},<?php echo maybe_json_encode($value)?>, value);

                            if (typeof value.country !== 'undefined') {
                                $('#<?php echo $field?>-country').val(value.country).trigger('change');
                            }
                            $("#<?php echo $field?>-fieldset-address").distpicker('destroy');
                            $("#<?php echo $field?>-fieldset-address").distpicker(value);

                            if (typeof value.street !== 'undefined') {
                                $("#<?php echo $field?>-street").val(value.street);
                            }
                        };
                    });
                })(jQuery);
            </script>
            <?php
        }else{
            $countries = require 'i18n/countries.php';
//            foreach ($countries as $code=>$name){
//                if(file_exists("./i18n/states/{$code}.php")){
//                    @include "i18n/states/{$code}.php";
//                }
//            }
            @include "i18n/states/CN.php";
            global $states;
            if($states){
                foreach ($states as $co=>$stateList){
                    foreach ($stateList as $k=>$v){
                        $states[$co][$k] = html_entity_decode($v);
                    }
                }
            }


            ?>
            <div class="d-flex flex-column align-items-start">
                <div id="<?php echo $field ?>-fieldset-country" class="d-flex d-flex-row " style="margin-bottom: 5px;">
                    <select id="<?php echo $field ?>-country"
                            class="form-control <?php echo esc_attr(join(' ', array_unique($this->data['class']))); ?>"
                            name="<?php echo $field ?>-country"
                            style="<?php echo $this->getCustomerStyleHtml(); ?>" <?php echo $this->data['disabled'] ? 'disabled' : ''; ?> <?php echo $this->data['readonly'] ? 'readonly' : ''; ?>   <?php echo $this->getCustomAttributeHtml(); ?> >
                        <?php
                        if ($this->data['empty_option'] || $this->get('searching')) {
                            ?>
                            <option value="">-- Country --</option> <?php
                        }

                        foreach ($countries as $countryCode=>$countryName) {
                            ?>
                            <option <?php echo $country == $countryName ? 'selected' : ''; ?>
                            value="<?php echo esc_attr($countryCode) ?>"><?php echo $countryName ?></option><?php
                        } ?>
                    </select>
                </div>

                <div id="<?php echo $field ?>-fieldset-address" >

                </div>
                <div id="<?php echo $field ?>-fieldset-street"
                     class="d-flex d-flex-row <?php echo !$this->data['street'] || $this->get('searching') ? 'hide' : ''; ?>"
                     style="margin-top: 5px;width: 100%;padding: 10px 0px;">
                    <textarea id="<?php echo $field ?>-street" rows="3" placeholder="Town / City,House number and street name, Apartment, suite, unit etc. "
                              class="form-control <?php echo esc_attr(join(' ', array_unique($this->data['class']))); ?>"
                              name="<?php echo $field ?>-street" <?php echo $this->data['disabled'] ? 'disabled' : ''; ?> <?php echo $this->data['readonly'] ? 'readonly' : ''; ?> <?php echo $this->getCustomAttributeHtml(); ?> ><?php echo esc_textarea($street) ?></textarea>
                </div>
            </div>
            <script type="text/javascript">
                (function($){
                    var countries = <?php echo maybe_json_encode($countries);?>;
                    var states = <?php echo maybe_json_encode($states);?>;
                    $("#<?php echo $field ?>-country").change(function(){
                        var code = $(this).val();
                        var html='';
                        var province = "<?php echo $province;?>";
                        if(typeof states[code]!=='undefined'){
                            html='<select class="form-control" id="<?php echo $field ?>-province" >';
                            for(var i in states[code]){
                                html+='<option value="'+states[code][i]+'" '+(province==states[code][i]?'selected':'')+'>'+states[code][i]+'</option>'
                            }
                            html+='</select>';
                        }

                        $("#<?php echo $field ?>-fieldset-address").html(html);
                    }).change();


                    <?php if($this->data['country'] ){
                    ?>$('#<?php echo $field?>-fieldset-country').removeClass('hide');
                    <?php
                    }else{
                    if(!($this->data['empty_option'] || $this->get('searching'))){
                    ?>$('#<?php echo $field?>-country').val('China');<?php
                    }

                    }?>

                    let getAddressValue = function () {
                        let result = {
                            country: 'China'
                        };
                        <?php if($this->data['country']){
                        ?>
                        var countryCode =  $('#<?php echo $field?>-country').val();
                        result.country = countryCode?countries[countryCode]:'';

                        <?php
                        }?>

                        var $province = $('#<?php echo $field ?>-fieldset-address').find('#<?php echo $field ?>-province');
                        if($province.length){
                            result.province = $province.val();
                        }

                        <?php if($this->data['street']){
                            ?>result.street = $('#<?php echo $field?>-street').val();<?php
                        }?>
                        return result;
                    };

                    $(document).bind("handle_<?php echo $form_id?>_submit", function (e, form) {
                        form.<?php echo esc_attr($this->key)?> = JSON.stringify(getAddressValue());
                    });

                    window.set_field_<?php echo $field?>_value = function (value) {
                        //value = $.extend({},<?php echo maybe_json_encode($value)?>, value);
                    };

                })(jQuery);
            </script>
            <?php
        }
    }
}