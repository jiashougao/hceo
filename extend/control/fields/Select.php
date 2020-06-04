<?php
namespace control\fields;

use think\Exception;
use think\Response;

class Select extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'class' => array(),
        'style' => array(),
        'placeholder' => '',
       // 'type' => 'select',
        'default'=>null,
        'value'=>'',
        'custom_attributes' => array (),
        'options'=>array()
    );

    public function preview($strip_tags = false,$values=null){
       $value = $this->getValue();

        if(!is_string($this->data['options'])&&is_callable($this->data['options'])){
            $this->data['options'] = call_user_func($this->data["options"]);
        }
       if(array_is_empty($this->data['options'])){
           return $value;
       }

       $value = isset($this->data['options'][$value])?$this->data['options'][$value]:$value;
        if($strip_tags){
            return $value;
        }
        return "<div  class='form-control-textarea-preview'>{$value}</div>";
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );

        if(!is_string($this->data['options'])&&is_callable($this->data['options'])){
            $this->data['options'] = call_user_func($this->data["options"]);
        }

        ?>
        <select class="form-control <?php echo esc_attr(join(' ',array_unique($this->data['class']))  ); ?>" name="<?php echo $field?>" id="<?php echo $field?>"  style="<?php echo $this->getCustomerStyleHtml(); ?>" <?php echo  $this->data['disabled']?'disabled':''; ?>   <?php echo $this->getCustomAttributeHtml(  ); ?> >
            <?php if($this->data['options']&&is_array($this->data['options'])){
                foreach($this->data['options'] as $k=>$v){
                    ?><option value="<?php echo esc_attr($k);?>" <?php echo $k==$this->getValue()?'selected':''?>><?php echo $v;?></option><?php
                }
            }?>
        </select>

        <script type="text/javascript">
            (function($,undefined){
               $(function(){
                   $('#<?php echo $field?>').change(function(){
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

    public function param(&$res,$call=null){
        $response = parent::param($res,$call);
        if(!is_string($this->data['options'])&&is_callable($this->data['options'])){
            $this->data['options'] = call_user_func($this->data['options']);
        }

        if(!is_null_or_empty($response)){
            if(!is_array($this->data['options'])||!isset($this->data['options'][$response])){
                throw new Exception(errorMessage(10002,array($this->data['title'])),10002);
            }
        }


        return $response;
    }
}