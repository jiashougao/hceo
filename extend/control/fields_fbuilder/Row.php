<?php
namespace control\fields_fbuilder;

use control\ControlFBuilder;

class Row extends \control\fields\Row
{
    public function handleClearData($objectType,$objectId){
        if(array_is_empty($this->data['items'])){
            return;
        }

        $builder = new ControlFBuilder();
        foreach ($this->data['items'] as $fieldKey=>$field){
            $builder->handleClearData($objectType,$objectId,$fieldKey,$field);
        }
    }
    public function handleClearSurvey($objectType,$objectId,$expo_user_id){
        if(array_is_empty($this->data['items'])){
            return;
        }

        $builder = new ControlFBuilder();
        foreach ($this->data['items'] as $fieldKey=>$field){
            $builder->handleClearSurvey($objectType,$objectId,$fieldKey,$field,$expo_user_id);
        }
    }
    public function handleSaveSurvey($objectType,$objectId,$values,$expo_user_id){
    	if(array_is_empty($this->data['items'])){
    		return;
    	}
    	
    	$builder = new ControlFBuilder();
    	foreach ($this->data['items'] as $fieldKey=>$field){
    		$builder->handleSaveSurvey($objectType,$objectId,$fieldKey,$field,$values,$expo_user_id);
    	}
    }
    
    public function handleSaveData($objectType,$objectId,$values){
        if(array_is_empty($this->data['items'])){
            return;
        }

        $builder = new ControlFBuilder();
        foreach ($this->data['items'] as $fieldKey=>$field){
            $builder->handleSaveData($objectType,$objectId,$fieldKey,$field,$values);
        }
        return null;
    }

    public static function generateScript(){
        ?>
        <script type="text/javascript">
            (function($,editor){
                editor.generate_row_field = function(fieldKey,data,cel){
                    return '';
                };

                editor.get_row_config=function(fieldKey,data){
                    return {};
                }
            })(jQuery,formBuilder);
        </script>
        <?php
    }

    public function generateField($form_id) {
        $items = $this->data['items'];
        if(array_is_empty($items)){
            return;
        }
        ?>
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
        <?php
    }

    public function param(&$res,$call=null){
        $items = $this->data['items'];
        if(array_is_empty($items)){
            $res[$this->key] = null;
            return null;
        }

        $results = array();
        $control = new \control\ControlFBuilder();
        foreach ($items as $fieldKey=>$field){
            $results[$this->key][$fieldKey] =  $control->param($fieldKey,$field,$res,$call);
        }
        return $results;
    }

    public function setValue(&$field,$values){
        $builder = new ControlFBuilder();
        $items = isset($field['items'])&&!array_is_empty($field['items'])?$field['items']:array();
        if(!count($items)){
            return;
        }
        $builder->setValues($items,$values);
        $field['items'] = $items;
    }
}