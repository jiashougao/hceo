<?php
namespace control\fields;

class Format extends Base{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'readonly' => false,
        'contenteditable'=>true,
        'class' => array(),
        'style' => array(
            'min-height'=>'80px',
            'height'=>'inherit'
        ),
        'keywords'=>array(),
        'placeholder' => '',
        'type' => 'text',
        'default'=>'',
        'value'=>'',
        'custom_attributes' => array ()
    );

    public function preview($strip_tags = false,$values=null){
        $value = $this->getValue();
        if($strip_tags){
            return $value;
        }

        $value =  $this->toHtml($value,$this->data['keywords']);
        return "<div  class='form-control-textarea-preview'>{$value}</div>";
    }

    public function param(&$res,$call=null){
        $value = parent::param($res,$call);
        $res[$this->key] = $this->toString($value);
        return $res[$this->key];
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );

        ?>
        <div contenteditable="<?php echo $this->data['contenteditable']?'true':'false';?>" class="form-control <?php echo esc_attr( join(' ',array_unique($this->data['class']))  ); ?>" name="<?php echo $field?>" id="<?php echo $field?>"  style="<?php echo $this->getCustomerStyleHtml(); ?>" placeholder="<?php echo esc_attr( $this->data['placeholder'] ); ?>" <?php echo $this->data['disabled']?'disabled':''; ?> <?php echo $this->data['readonly']?'readonly':''; ?>   <?php echo $this->getCustomAttributeHtml(  ); ?> ><?php echo $this->toHtml($this->getValue(),$this->data['keywords']); ?></div>
        <div style="margin-top:10px;">
            <?php foreach ($this->data['keywords'] as $key=>$title){
                ?><span style="margin-right: 10px;cursor:pointer;border-radius: 10px!important;" class="label label-primary label-sm <?php echo $field?>-key" data-key="<?php echo esc_attr($key)?>"><i class="fa fa-tag"></i> <?php echo $title;?></span><?php
            }?>
        </div>
        <script type="text/javascript">
            (function($,undefined){
                $(function(){
                    let lastEditRange = null;
                    let getEditorSection = function(){
                        if (!window.getSelection) {
                            return;
                        }
                        let section = getSelection();

                        lastEditRange =section&&section.type!=='None'?section .getRangeAt(0):null;
                    };
                    $('#<?php echo $field?>').focus(getEditorSection)
                        .click(getEditorSection)
                        .keyup(getEditorSection);

                    $('.<?php echo $field?>-key').click(function(){
                        let html = '<pre contenteditable="false" class="mt-code" data-key="'+$(this).data('key')+'">'+$(this).text()+'</pre>';

                        let selection = getSelection();
                        if (lastEditRange) {
                            selection.removeAllRanges();
                            selection.addRange(lastEditRange);
                        }

                        let sel =selection;
                        if (sel&&sel.getRangeAt && sel.rangeCount) {
                            let range = sel.getRangeAt(0);
                            range.deleteContents();
                            let el = document.createElement("div");
                            el.innerHTML = html;
                            let frag = document.createDocumentFragment(), node, lastNode;
                            while ( (node = el.firstChild) ) {
                                lastNode = frag.appendChild(node);
                            }

                            range.insertNode(frag);
                            if (lastNode) {
                                range = range.cloneRange();
                                range.setStartAfter(lastNode);
                                range.collapse(true);
                                sel.removeAllRanges();
                                sel.addRange(range);
                            }
                        }else{
                            $('#<?php echo $field?>').append(html);
                        }

                        $('#<?php echo $field?>').keyup();
                    });

                    $('#<?php echo $field?>').keyup(function(){
                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'keyup',
                            value:$('#<?php echo $field?>').html()
                        });
                    }).focus(function(){
                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'focus',
                            value:$('#<?php echo $field?>').html()
                        });
                    }).blur(function(){
                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'blur',
                            value:$('#<?php echo $field?>').html()
                        });
                    });

                    $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                        form.<?php echo esc_attr($this->key)?> = $('#<?php echo $field?>').html();
                    });
                    window.set_field_<?php echo $field?>_value = function(value){
                        $('#<?php echo $field?>').html(value).trigger('change');
                    };
                });
            })(jQuery);
        </script>
        <?php
    }

    public function toString($source){
        if(!$source){
            return $source;
        }

        return preg_replace_callback("/<pre\s+contenteditable=\"false\"\s+class=\"mt\-code\"\s+data\-key=\"([a-zA-Z\-\_\d\.]+)\">[^<]+<\/pre>/",function($match){
            if($match&&count($match)===2){
                return '{'.$match[1].'}';
            }

            return $match[0];
        },$source);
    }

    public function toHtml($source){
        $formats = $this->data['keywords'];
        if(!$source||array_is_empty($formats)){
            return $source;
        }
        return preg_replace_callback("/\{([a-zA-Z\d\-_\.]+)\}/",function($match)use($formats){
            if($match&&count($match)===2&&isset($formats[$match[1]])){
                return '<pre contenteditable="false" class="mt-code" data-key="'.$match[1].'">'.$formats[$match[1]].'</pre>';
            }

            return $match[0];
        },$source);
    }
}