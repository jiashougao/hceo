<?php
namespace control\fields;

use think\Response;

class Editor extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'default'=>'',
        'value'=>''
    );

    public function preview($strip_tags = false,$values=null){
        $value = $this->getValue();

        if($strip_tags){
            return $value;
        }

        return "<div class='form-control-textarea-preview'>{$value}</div>";
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );
        if(defined('IS_EDITOR_INNER')){
            ?><img src="https://server.messecloud.com/static/dist/img/common/editor.png" style="width:100%;height:auto;min-width: 320px;" /> <?php
        }else{
            if(!defined('XC_UEDITOR_SCRIPT')){
                define('XC_UEDITOR_SCRIPT',true);

                ?>
                <script src="/static/plugins/ueditor/ueditor.config.js?v=<?php echo APP_VERSION ?>"></script>
                <script src="/static/plugins/ueditor/ueditor.all.js?v=<?php echo APP_VERSION ?>"></script>
                <script src="/static/plugins/ueditor/third-party/zeroclipboard/ZeroClipboard.js?v=<?php echo APP_VERSION ?>"></script>
                <?php
            }

            ?>
            <script id="<?php echo $field;?>" type="text/plain"><?php echo $this->getValue()?></script>
            <script type="text/javascript">
                jQuery(function($){
                    let editor = UE.getEditor('<?php echo $field;?>',{
                        scaleEnabled:true,
                        allowDivTransToP:false,
                        zIndex:99,
                        initialFrameHeight:200
                    });
                    let content = null;
                    editor.ready(function(){
                        <?php if($this->data['disabled']){ ?>
                        editor.setDisabled();
                        <?php }?>
                        content = editor.getContent();
                        editor.addListener("focus",function(){
                            $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                                column:"<?php echo $field?>",
                                event:'focus',
                                value:editor.getContent()
                            });
                        });
                        editor.addListener("blur",function(){
                            $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                                column:"<?php echo $field?>",
                                event:'blur',
                                value:editor.getContent()
                            });
                        });
                        editor.addListener("contentChange",function(){
                            $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                                column:"<?php echo $field?>",
                                event:'keyup',
                                value:editor.getContent()
                            });
                        });
                        window.<?php echo $form_id?>_<?php echo esc_attr($this->key)?> = editor;
                    });
                    $(document).bind("handle_<?php echo $form_id?>_reset",function(e,form){
                        editor.setContent(content);
                    });
                    $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                        form.<?php echo esc_attr($this->key)?> = editor.getContent();
                    });
                    window.set_field_<?php echo $field?>_value = function(value){
                        editor.setContent(value);
                    };
                });
            </script>
            <?php
        }

    }

}