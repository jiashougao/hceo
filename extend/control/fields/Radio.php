<?php
namespace control\fields;

use think\Exception;
use think\Response;

class Radio extends Base
{
    public $default = array (
        'required'=>false,
        'readonly'=>false,
        'disabled' => false,
        'class' => array(),
        /**
         * 标识是否默认不选中
         */
        'empty_option'=>false,
        'style' => array(),
        'option_other'=>null,
        'direction'=>'row',//row|column
        'placeholder' => '',
        'type' => 'select',
        'default'=>null,
        'value'=>'',
        'custom_attributes' => array (),
        'options'=>array()
    );
    public function preview($strip_tags = false,$values=null)
    {
        $value =$this->getValue();

        if(!is_string($this->data['options'])&&is_callable($this->data['options'])){
            $this->data['options'] = call_user_func($this->data["options"]);
        }
        if(!array_is($this->data['options'])){
            $this->data['options'] = array();
        }

        if($strip_tags){
            return isset($this->data['options'][$value])?$this->data['options'][$value]:$value;
        }

        ob_start();
        ?>
        <ul style="list-style: none;padding:0;margin:0">
            <li><div style="max-width:160px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;"><?php echo isset($this->data['options'][$value])?$this->data['options'][$value]:$value;?></div></li>
        </ul>
        <?php
        return ob_get_clean();
    }
    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );

        if(!is_string($this->data['options'])&&is_callable($this->data['options'])){
            $this->data['options'] = call_user_func($this->data["options"]);
        }
        if(!array_is($this->data['options'])){
            $this->data['options'] = array();
        }
        if(!array_is($this->data['option_other'])){
            $this->data['option_other'] = array();
        }
        $option_other = parse_args($this->data['option_other'],array(
            'enable'=>false,
            'title'=>'其他选项',
            'placeholder'=>null,
            'required'=>false
        ));
        $value = $this->getValue();
        if(!$option_other['enable']){
            $value = isset($this->data['options'][$value])?$value:array_key_first($this->data['options']);
        }
        if(!array_is($this->data['options'])){
            $this->data['options'] = array();
        }
        ?>
        <div class="<?php echo $this->data['direction']=='row'?'mt-radio-inline':'mt-radio-list'?>">
            <?php
                if($this->data['empty_option']||$this->get('searching')){
                    ?>
                    <label class="mt-radio mt-radio-outline"> 请选择
                        <input type="radio" class="xc-radio" name="<?php echo $field;?>" <?php echo  $this->data['disabled']?'disabled':''; ?>  <?php echo !$value?"checked":"";?> value="">
                        <span></span>
                    </label>
                    <?php
                }

                foreach($this->data['options'] as $k=>$v){
                    ?>
                    <label class="mt-radio mt-radio-outline"> <?php echo $v?>
                        <input type="radio" class="xc-radio" name="<?php echo $field;?>" <?php echo  $this->data['disabled']?'disabled':''; ?>  <?php echo $value==$k?"checked":"";?> value="<?php echo esc_attr($k)?>">
                        <span></span>
                    </label>
                    <?php
                }?>
        </div>
        <?php

        if($option_other['enable']){
            ?>
            <div class="form-group">
                <label class="mt-radio mt-radio-outline">
                    <?php echo $option_other['title']?$option_other['title']:"其他选项";?>

                    <input type="radio" class="xc-radio" name="<?php echo $field;?>" <?php echo $value&&!isset($this->data['options'][$value])?"checked":"";?> value="" id="<?php echo $field?>-other-option"/>
                    <span></span>
                </label>
                <?php if($option_other['required']){
                    ?>
                    <span class="required"> * </span>
                    <?php
                }?>
                <input type="text" class="form-control" placeholder="<?php echo esc_attr($option_other['placeholder'])?>" name="<?php echo $field?>-other" id="<?php echo $field?>-other" value="<?php echo esc_attr($value&&!isset($this->data['options'][$value])?$value:"")?>"  <?php echo  $this->data['disabled']?'disabled':''; ?>   <?php echo  $this->data['readonly']?'readonly':''; ?> />
            </div>
            <?php
        }
        ?>
        <script type="text/javascript">
            (function($,undefined){
                $(function(){
                    let getRadioValue = function(){
                        if($("#<?php echo $field?>-other-option:checked").length>0){
                            return $.trim($("#<?php echo $field?>-other").val());
                        }

                        return $(".xc-radio[name=<?php echo $field;?>]:checked").val();
                    };
                    $("#<?php echo $field?>-other").keyup(function(){
                        $("#<?php echo $field?>-other-option").prop('checked',true);
                    });
                    $('.xc-radio[name=<?php echo $field;?>]').change(function(){
                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'keyup',
                            value:getRadioValue()
                        });
                    });
                    $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                        form.<?php echo esc_attr($this->key)?> = getRadioValue();
                    });

                    window.set_field_<?php echo $field?>_value = function(value){
                        if(!value){
                            return;
                        }
                        let options = <?php echo maybe_json_encode($this->data['options']);?>;
                        $(".xc-radio[name=<?php echo $field;?>]:checked").prop('checked',false);
                        $(".xc-radio[name=<?php echo $field;?>]").each(function(){
                            $(this).prop('checked',$(this).val()==value);
                        });

                        if(!_.find(options,function(m){return m==value;})){
                            $(".xc-radio[name=<?php echo $field;?>]:checked").prop('checked',false);
                            $("#<?php echo $field?>-other-option").prop("checked",true);
                            $("#<?php echo $field?>-other").val(value);
                        }
                    };
                });
            })(jQuery);
        </script>
        <?php
    }

    public function param(&$res,$call=null){
        $response = parent::param($res,$call);

        if(!is_string($this->data['options'])&&is_callable($this->data['options'])){
            $this->data['options'] = call_user_func($this->data['options']);
        }

        if(!is_null_or_empty($response)){
            if(!is_array($this->data['options'])||!isset($this->data['options'][$response])){
                if(!array_is($this->data['option_other'])||!isset($this->data['option_other']['enable'])||!$this->data['option_other']['enable']){
                    throw new Exception(errorMessage(10002,array($this->data['title'])),10002);
                }
            }
        }
        return $response;
    }
}