<?php
namespace control\fields;

use org\helper\ImageHelper;

class Qrcode extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,'readonly' => false,
        'class' => array(),
        'style' => array(),
        'placeholder' => '',
        'default'=>null,'value'=>'',
        'custom_attributes' => array ()
    );

    public function preview($strip_tags = false,$values=null){
        $value = $this->getValue();

        if($strip_tags){
            return $value;
        }
        $url = ImageHelper::qrcode($value);
        return "<div><img src=\"{$url}\" style=\"width:120px;height:120px;\"/><a class=\"btn btn-xs btn-success\" download=\"qrcode.png\" href=\"{$url}\">下载</a></div>
                <a href=\"".esc_attr($value)."\" target='_blank'><div  class='form-control-textarea-preview'>{$value}</div></a>";
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );
        ?>
        <div class="d-flex flex-column">
            <div class="d-flex flex-row align-items-end">
                <img id="<?php echo $field?>-qrcode" src="" class="hide" style="width:80px;height:80px;"/>
                <div style="margin-left:5px;">
                    <a class="btn btn-xs btn-success hide"  download="qrcode.png"  id="<?php echo $field?>-download">下载</a>
                </div>
            </div>

            <input type="<?php echo esc_attr( $this->data['type'] ); ?>" class="form-control <?php echo esc_attr( join(' ',array_unique($this->data['class']))  ); ?>" name="<?php echo $field?>" id="<?php echo $field?>"  style="<?php echo $this->getCustomerStyleHtml(); ?>" value="<?php echo esc_attr( $this->getValue() ); ?>" placeholder="<?php echo esc_attr( $this->data['placeholder'] ); ?>" <?php echo  $this->data['disabled']?'disabled':''; ?> <?php echo  $this->data['readonly']?'readonly':''; ?>   <?php echo $this->getCustomAttributeHtml(  ); ?> />
        </div>

        <script type="text/javascript">
            (function($,undefined){
                $(function(){
                    $('#<?php echo $field?>').keyup(function(){
                        let base64 = new BASE64();
                        let val = $(this).val();
                        if(!val){
                            $('#<?php echo $field?>-qrcode,#<?php echo $field?>-download').addClass("hide");
                            return;
                        }
                        let url = window.GLOBALS['qrcodeUrl']+'?data='+base64.encode(val);
                        $('#<?php echo $field?>-qrcode').attr('src',url).removeClass("hide");
                        $('#<?php echo $field?>-download').attr('href',url).removeClass("hide");
                    }).keyup();

                    $('#<?php echo $field?>').keyup(function(){
                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'keyup',
                            value:$('#<?php echo $field?>').val()
                        });
                    }).focus(function(){
                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'focus',
                            value:$('#<?php echo $field?>').val()
                        });
                    }).blur(function(){
                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'blur',
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

}