<?php
namespace control\fields_bootstrap;

class Datetime extends \control\fields\Datetime
{

    public function generateField($form_id) {
        $defaults = array (
            'title' => '',
            'required'=>false,
            'description' => ''
        );

        $data = parse_args ( $this->data, $defaults );

        ?>
        <div class="form-group <?php echo esc_attr($this->key)?>">
            <label class="col-md-2 control-label">
                <?php echo $data['title'];?>
                <?php if($data['required']){
                    ?>
                    <span class="required"> * </span>
                    <?php
                }?>
            </label>
            <div class="col-md-10">
                <?php parent::generateField($form_id)?>
                <span class="help-block"><?php echo $data['description'] ?></span>
            </div>
        </div>
        <?php
    }
}