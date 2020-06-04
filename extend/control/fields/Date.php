<?php
namespace control\fields;

use org\helper\UrlHelper;

class Date extends Base
{
    public $default = array (
        'required'=>false,
        'readonly' => false,
        'class' => array(),
        'style' => array(),
        'placeholder' => '',
        'default'=>null,
        'value'=>null,
        'custom_attributes' => array ()
    );
    public function preview($strip_tags = false,$values=null){
        $value = $this->getValue();
        if(!$value){
            return null;
        }
        if($strip_tags){
            return $value;
        }

        return "<code>".date('Y-m-d',strtotime($value))."</code>";
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );
        $value = $this->getValue();
        if(!defined("XC_DATE_SCRIPTS")){
            define("XC_DATE_SCRIPTS",true);
            if(!UrlHelper::isMinWebClient()){
                ?> <script src="<?php echo assets_url('/static/plugins/laydate/laydate.js')?>"></script><?php
            }else{
                ?> <script src="<?php echo assets_url('/static/plugins/jdate/jdate.min.js')?>"></script><?php
            }
        }
        ?>
        <input type="text" autoComplete="off" class="form-control <?php echo esc_attr( join(' ',array_unique($this->data['class']))  ); ?>" name="<?php echo $field?>" id="<?php echo $field?>"  style="<?php echo $this->getCustomerStyleHtml(); ?>" value="<?php echo esc_attr( $value?date('Y-m-d',strtotime($value)) :""); ?>" placeholder="<?php echo esc_attr( $this->data['placeholder'] ); ?>" <?php echo  $this->data['readonly']?'readonly':''; ?>   <?php echo $this->getCustomAttributeHtml(  ); ?> />
        <script type="text/javascript">
            (function($,undefined){
                $(function(){
                    <?php  if(!$this->data['readonly']){
                        if(!UrlHelper::isMinWebClient()){
                            ?>
                            laydate.render({
                                elem: '#<?php echo $field?>',
                                format: 'yyyy-MM-dd',
                                lang:'<?php echo get_lang()==='en-us'?'en':''?>',
                                trigger: 'click'
                            });
                <?php  }else{  ?>
                            var config = {
                                el: '#<?php echo $field?>',
                                format: 'YYYY-MM-DD'
                            };
                            <?php if(get_lang()!='zh-cn'){ ?>
                            config.lang = {
                                title: 'Select time',
                                cancel: 'cancel',
                                confirm: 'confirm',
                                year: '',
                                month: '',
                                day: '',
                                hour: 'h',
                                min: 'm',
                                sec: 's'
                            };
                            <?php }?>
                            new Jdate(config);
                    <?php
                        }
                    } ?>


                    $('#<?php echo $field?>').change(function(){
                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'keyup',
                            value:$('#<?php echo $field?>').val()
                        });
                    });

                    $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                        form.<?php echo esc_attr($this->key)?> = $('#<?php echo $field?>').val();
                    });
                    window.set_field_<?php echo $field?>_value = function(value){
                        $('#<?php echo $field?>').val(value).trigger('change');
                    };
                });
            })(jQuery);
        </script>
        <?php
    }

    public function param(&$res,$call=null){
        $value = parent::param($res,$call);
        $res[$this->key] = empty($value)?null:$value;
        return $res[$this->key];
    }
}