<?php

namespace control\fields;

class Download extends Base {
    public $default = array(
        'required' => false,
        'disabled' => false,
        'btn_title'=>'',
        'file' => '',
        'class' => array(),
        'style' => array(),
        'default' => null,
        'value' => '',
        'custom_attributes' => array()
    );

    public function generateField($form_id) {
        $data=$this->data;
        $btn_title=$data['btn_title']?$data['btn_title']:'文件下载';
        $file = maybe_json_decode($data['file']);
        $file_url=$file?$file[0]['url']:'';
        $file_title=$file?$file[0]['title']:'';
        ?>
        <a class="btn btn-primary"
           <?php if($file_title):?>download="<?php echo $file_title;?>"<?php endif;?>
           href="<?php echo $file_url;?>"
        ><?php echo $btn_title;?></a>
        <?php
    }

}