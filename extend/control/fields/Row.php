<?php
namespace control\fields;

use control\Control;

class Row extends Base
{
    public $defaults = array (
        'items'=>array()
    );

    public function preview($strip_tags = false,$values=null)
    {
        return null;
    }

    public function generateField($form_id) {
        $items = $this->data['items'];
        if(array_is_empty($items)){
            return;
        }
        $control = new Control();
        foreach ($items as $fieldKey=>$field){
            $control->generateField($form_id,$fieldKey,$field);
        }
    }

    public function param(&$res,$call=null){
        $items = $this->data['items'];
        if(array_is_empty($items)){
            $results[$this->key] = null;
            return null;
        }
        $results = array();
        $control = new Control();
        foreach ($items as $fieldKey=>$field){
            $results[$this->key][$fieldKey] =  $control->param($fieldKey,$field,$res,$call);
        }
        return $results;
    }

    public function setValue(&$field,$values){
        $builder = new Control();
        $items = isset($field['items'])&&!array_is_empty($field['items'])?$field['items']:array();
        if(!count($items)){
            return;
        }
        $builder->setValues($items,$values);
        $field['items'] = $items;
    }
}