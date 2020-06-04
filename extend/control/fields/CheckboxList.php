<?php
namespace control\fields;

use think\Exception;

class CheckboxList extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'readonly' => false,
        'class' => array(),
        'options'=>array(),
        'style' => array(),
        'placeholder' => '',
        'option_other'=>null,
        'default'=>null,
        'value'=>'',
        'custom_attributes' => array () ,
        'excelToData'=>__CLASS__.'::excelToData'
    );

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
        if(array_is_empty($data)){
            return $excelOrRealData;
        }
        $dataArray = array();
        foreach ($data as $key => $value){
            if(empty($value)){
                continue;
            }
            $dataArray[] = $value;
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

    public function preview($strip_tags = false,$values=null)
    {
        $value =maybe_json_decode($this->getValue(),true);
        if(array_is_empty($value)){$value=array();}

        if(!is_string($this->data['options'])&&is_callable($this->data['options'])){
            $this->data['options'] = call_user_func($this->data["options"]);
        }
        if(!array_is($this->data['options'])){
            $this->data['options'] = array();
        }

        if($strip_tags){
            $html = '';
            foreach ($value as $val){
                if($html){$html.=";";}
                $html.=isset($this->data['options'][$val])?$this->data['options'][$val]:$val;
            }
            return $html;
        }

        ob_start();
        ?>
        <ul style="list-style: none;padding:0;margin:0">
            <?php foreach ($value as $val){
                ?><li><div style="max-width:160px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;"><?php echo isset($this->data['options'][$val])?$this->data['options'][$val]:$val;?></div></li><?php
            }?>
        </ul>
        <?php
        return ob_get_clean();
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );
        $selected=maybe_json_decode($this->getValue(),true);
        if(!array_is($selected)){
            $selected=[];
        }
        if(!is_string($this->data['options'])&&is_callable($this->data['options'])){
            $this->data['options'] = call_user_func($this->data["options"]);
        }
        if(!array_is($this->data['options'])){
            $this->data['options'] = array();
        }
        ?><div class="mt-checkbox-inline"><?php
              foreach($this->data['options'] as $k=>$v){?>
                  <label class="mt-checkbox mt-checkbox-outline">
                      <?php echo $v; ?>
                      <input type="checkbox" name="<?php echo $field; ?>" value="<?php echo esc_attr($k); ?>" <?php echo in_array($k,$selected)?'checked':''; ?> <?php echo  $this->data['disabled']?'disabled':''; ?>   <?php echo $this->getCustomAttributeHtml(  ); ?> data-row-name="<?php echo esc_attr($v); ?>" />
                      <span></span>
                  </label>
        <?php }?>
        </div>
        <?php
        if(!array_is($this->data['option_other'])){
            $this->data['option_other'] = array();
        }
        $option_other = parse_args($this->data['option_other'],array(
            'enable'=>false,
            'title'=>'其他选项',
            'placeholder'=>null,
            'required'=>false
        ));
        $options = $this->data['options'];
        if($option_other['enable']){
            $option = array_first($selected,function($m)use($options){
                return !isset($options[$m]);
            })
            ?>
            <div class="form-group">
                <label>
                    <?php echo $option_other['title']?$option_other['title']:"其他选项";?>
                    <?php if($option_other['required']){
                        ?>
                        <span class="required"> * </span>
                        <?php
                    }?>
                </label>
                <input type="text" class="form-control" placeholder="<?php echo esc_attr($option_other['placeholder'])?>" <?php echo  $this->data['disabled']?'disabled':''; ?>  name="<?php echo $field?>-other" id="<?php echo $field?>-other" value="<?php echo esc_attr($option)?>" />
            </div>
            <?php
        }
        ?>
        <script type="text/javascript">
            (function($,undefined){

                $(function(){
                    let getValues=function(){
                        let values = [];
                        $(':checkbox:checked[name=<?php echo $field; ?>]').each(function(){
                            values.push($(this).val());
                        });

                        <?php if($option_other['enable']){
                        ?>
                        let other = $.trim($("#<?php echo $field?>-other").val());
                        if(other&&!_.find(values,function(m){return m==other})){
                            values.push(other);
                        }
                        <?php
                        }?>

                        return _.unique(values);
                    };
                    $(':checkbox[name=<?php echo $field; ?>]').change(function(){
                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'keyup',
                            value: JSON.stringify(getValues())
                        });
                    });

                    $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                        form.<?php echo esc_attr($this->key)?> = JSON.stringify(getValues());
                    });

                    window.set_field_<?php echo $field?>_value = function(value){
                        if(!value||!_.isArray(value)){
                            return;
                        }
                        let options = <?php echo maybe_json_encode($options);?>;
                        $(':checkbox:checked[name=<?php echo $field; ?>]').each(function(){
                            let v = $(this).val();
                            $(this).prop("checked",_.find(value,function(m){return m==v;}));
                        });

                        for(let key in options){
                            if(_.all(value,function(m){return m!=key;})){
                                $("#<?php echo $field?>-other").val(key);
                                break;
                            }
                        }
                    };
                });
            })(jQuery);
        </script>
        <?php
    }

    public function param(&$res, $call = null)
    {
        $value =  parent::param($res, $call);
        $data = maybe_json_decode($value,true);
        if(isset($this->data['required'])&&$this->data['required']){
            if(!$data||!count($data)){
                throw new Exception(errorMessage(10000,array($this->data['title'])),10000);
            }
        }

        return $value;
    }
}