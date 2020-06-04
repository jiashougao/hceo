<?php
namespace control\fields;

class Color extends Base
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

        return "<span style=\"min-width:20px;min-height:10px;padding:5px;background-color:{$value}\">{$value}</span>";
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );
        ?>
        <input type="text" data-opacity="true" autocomplete="off" class="form-control <?php echo esc_attr( join(' ',array_unique($this->data['class']))  ); ?>" name="<?php echo $field?>" id="<?php echo $field?>"  style="<?php echo $this->getCustomerStyleHtml(); ?>" value="<?php echo esc_attr( $this->getValue() ); ?>" placeholder="<?php echo esc_attr( $this->data['placeholder'] ); ?>" <?php echo  $this->data['disabled']?'disabled':''; ?> <?php echo  $this->data['readonly']?'readonly':''; ?>   <?php echo $this->getCustomAttributeHtml(  ); ?> />
        <script type="text/javascript">
            (function($,undefined){
                $(function(){
                    $('#<?php echo $field?>').minicolors({
                        changeDelay:800
                    });

                    $('#<?php echo $field?>').on('change',function(){
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
                        $('#<?php echo $field?>').minicolors('value',value);
                    };
                });
            })(jQuery);
        </script>
        <?php
    }

}