<?php
namespace control\fields;

use control\Control;

class Html extends Base
{
    public $defaults = array (
        'description'=>array()
    );

    public function generateField($form_id) {
        ?><div class="form-group">
            <div class="col-md-12">
                <?php echo $this->data['description']; ?>
                <span class="help-block"></span>
            </div>
        </div><?php
    }

    public function param(&$res,$call=null){
        return null;
    }

    public function preview($strip_tags = false,$values=null)
    {
       return null;
    }

    public function setValue(&$field,$values){

    }
}