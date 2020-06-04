<?php
namespace control\fields;

class Hidden extends Base
{

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );
        ?>
        <input type="hidden" name="<?php echo $field?>" id="<?php echo $field?>" value="<?php echo esc_attr( $this->getValue() ); ?>" />
        <script type="text/javascript">
            (function($,undefined){
                $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                    form.<?php echo esc_attr($this->key)?> = $('#<?php echo $field?>').val();
                });
                window.set_field_<?php echo $field?>_value = function(value){
                    $('#<?php echo $field?>').val(value).trigger('change');
                };
            })(jQuery);
        </script>
        <?php
    }

}