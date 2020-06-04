<?php
namespace control\fields;

class Checkbox extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'class' => array(),
        'style' => array(),
        'placeholder' => '',
        'options'=>array(

        ),
        'default'=>null,
        'value'=>'',
        'custom_attributes' => array ()
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

        $value = isset($this->data['options'][$value])?$this->data['options'][$value]:$value;
        if($strip_tags){
            return $value;
        }
        ob_start();
        ?>
        <div style="max-width:160px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;"><?php echo $value;?></div>
        <?php
        return ob_get_clean();
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );

        ?>
        <input  data-on-text="<?php echo __('是')?>" data-off-text="<?php echo __('否')?>" data-size="small" type="checkbox" class="make-switch <?php echo esc_attr( join(' ',array_unique($this->data['class'])) ); ?>" name="<?php echo $field?>" id="<?php echo $field?>"  style="<?php echo $this->getCustomerStyleHtml(); ?>" value="yes" <?php echo $this->getValue()=='yes'?'checked':''; ?> placeholder="<?php echo esc_attr( $this->data['placeholder'] ); ?>" <?php echo  $this->data['disabled']?'disabled':''; ?>  <?php echo $this->getCustomAttributeHtml(  ); ?> />

        <script type="text/javascript">
            (function($,undefined){
                $(function(){
                    $("#<?php echo $field?>").bootstrapSwitch({
                        onSwitchChange:function(event, state) {
                            $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                                column:"<?php echo $field?>",
                                event:'keyup',
                                value: $('#<?php echo $field?>:checked').length>0?'yes':'no'
                            });
                        }
                    });

                    $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                        form.<?php echo esc_attr($this->key)?> = $('#<?php echo $field?>:checked').length>0?'yes':'no';
                    });
                    window.set_field_<?php echo $field?>_value = function(value){
                        $('#<?php echo $field?>').bootstrapSwitch('state', value==='yes');
                    };
                });
            })(jQuery);
        </script>
        <?php
    }

}