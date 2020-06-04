<?php
namespace control\fields_bootstrap;

class Subtitle extends \control\fields\Base
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
            <h3 class="col-md-2 control-label">
                <?php echo $data['title'];?>
            </h3>
            <div class="col-md-10">
            </div>
        </div>
        <?php
    }
}