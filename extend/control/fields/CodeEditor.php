<?php
namespace control\fields;

use think\Response;

class CodeEditor extends Base
{
    public $default = array (
        'required'=>false,
        'readonly' => false,
        'default'=>'',
        'value'=>''
    );

    public function preview($strip_tags = false,$values=null){
        $value = $this->getValue();

        if($strip_tags){
            return $value;
        }

        return "<div  class='form-control-textarea-preview'>{$value}</div>";
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );
        ?>
        <textarea class="form-control" id="<?php echo $field;?>" rows="3"><?php echo esc_textarea($this->getValue())?></textarea>
        <script type="text/javascript">
            jQuery(function($){
                var editor = CodeMirror.fromTextArea(document.getElementById("<?php echo $field;?>"), {
                    mode: "text/html",
                    //显示行号
                    smartIndent: true,
                    //显示行号
                    lineNumbers: true,
                    indentUnit: 4,         // 缩进单位为4
                    styleActiveLine: true, // 当前行背景高亮
                    matchBrackets: true,   // 括号匹配
                    lineWrapping: true,    // 自动换行
                    theme:'dracula',
                    readOnly: <?php echo $this->data['readonly']?'true':'false'?>
                });
                editor.on("blur", function () {
                    $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                        column:"<?php echo $field?>",
                        event:'keyup',
                        value: editor.getValue()
                    });
                });
                $(document).bind("handle_<?php echo $form_id?>_reset",function(e,form){
                    editor.setValue(content);
                });
                $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                    form.<?php echo esc_attr($this->key)?> = editor.getValue();
                });
                window.set_field_<?php echo $field?>_value = function(value){
                    editor.setValue(value);
                };
            });
        </script>
        <?php
    }

}