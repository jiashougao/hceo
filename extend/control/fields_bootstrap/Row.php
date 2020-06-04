<?php
namespace control\fields_bootstrap;

use control\ControlBootstrap;
use control\ControlFBuilder;

class Row extends \control\fields\Row
{
   public $defaults = array (
        'items'=>array()
   );
    public function generateField($form_id) {
        $items = $this->data['items'];
        if(array_is_empty($items)){
            return;
        }
        ?>
        <div class="form-group <?php echo esc_attr($this->key)?>">
            <label class="col-md-2 control-label">

            </label>
           <div class="col-md-10">
                <div class="row">
                    <?php
                    $control = new ControlFBuilder();
                    $count = count($items);
                    foreach ($items as $fieldKey=>$field){
                        ?>
                        <div class="col-md-<?php echo round(12/$count);?> col-lg-<?php echo round(12/$count);?> col-xs-<?php echo round(12/$count);?> col-sm-<?php echo round(12/$count);?> col-<?php echo round(12/$count);?>">
                            <?php $control->generateField($form_id,$fieldKey,$field);?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
           </div>
        </div>
        <?php
    }

    public function param(&$res,$call=null){
        $items = $this->data['items'];
        if(array_is_empty($items)){
            $res[$this->key] = null;
            return null;
        }

        $results = array();
        $control = new \control\ControlBootstrap();
        foreach ($items as $fieldKey=>$field){
            $results[$this->key][$fieldKey] =  $control->param($fieldKey,$field,$res,$call);
        }
        return $results;
    }

    public function setValue(&$field,$values){
        $builder = new ControlBootstrap();
        $items = isset($field['items'])&&!array_is_empty($field['items'])?$field['items']:array();
        if(!count($items)){
            return;
        }
        $builder->setValues($items,$values);
        $field['items'] = $items;
    }
}