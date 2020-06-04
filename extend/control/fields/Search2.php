<?php
namespace control\fields;

use think\Exception;
use think\Response;

class Search2 extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'class' => array(),
        'style' => array(
            'width'=>'100%'
        ),
        'placeholder' => '',
        'default'=>array(),
        'value'=>'',
        'multi'=>0,
        'action'=>null,

        //额外的过滤条件
        'filter'=>array(
            'state'=>'active'
        ),
        //对预览数据格式化
        'preview_item'=>null,
        'custom_attributes' => array ()
    );

    public function getValue($format=false){
        if(!$format){
            return parent::getValue();
        }
        $value = parent::getValue();
        $value =$value&&is_numeric($value)?[$value]: maybe_json_decode($value);
        if($value&&!is_array($value)){
            //兼容老数据
            $value = [$value];
        }

        //需要留空 触发filter
        if(array_is_empty($value)){
            $value=[null];
        }
        return $value;
    }

    public static function initFilter(){
        static $inited;
        if($inited){
            return;
        }
        $inited = true;
        do_action('app_search2_init');
    }

    public function preview($strip_tags = false,$values=null){
        $value = $this->getValue(true);
        self::initFilter();
        $items = array();
        foreach ($value as $id){
            $m = apply_filters("app_search2_{$this->get('action')}_get_item",[],$id,maybe_json_decode($this->data['filter']),$values/*可能为Null*/);//db_default($this->data['dbName'])->where($this->data['idColumn'],$id)->find();
            if(!$m){
                continue;
            }

            $items[]=$m;
        }

        if($strip_tags){
            $str ="";
            foreach ($items as $item){
                if($str){$str.=",";}
                $str.= $item['id'];
            }

            return $str;
        }

        ob_start();
        ?>
        <ul style="list-style: none;padding:0;margin:0">
            <?php
            $preview_item = $this->get('preview_item');
            foreach ($items as $file){
                ?><li><div  class='form-control-textarea-preview'><?php echo $preview_item?$preview_item($file):$file['title']?></div></li><?php
            }?>
        </ul>
        <?php
        return ob_get_clean();
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );
        $request=array(
            'action'=>$this->data['action'],
            'filter'=>maybe_json_encode($this->data['filter']),
        );

        ksort($request);
        reset($request);
        $request['xc-sign'] = md5(http_build_query($request).config('app.auth_salt'));

        $value = $this->getValue(true);
        if(!is_array($value)){
            $value=array();
        }
        self::initFilter();
        $items = array();
        foreach ($value as $id){
            $m = apply_filters("app_search2_{$this->get('action')}_get_item",null,$id,maybe_json_decode($this->data['filter']),null);//db_default($this->data['dbName'])->where($this->data['idColumn'],$id)->find();
            if(!$m){
                continue;
            }
            $items[]=$m;
        }
        ?>
        <select data-custom_params="<?php echo esc_attr(maybe_json_encode(array(
            'action'=>$request['action'],
            'filter'=>$request['filter'],
            'xc-sign'=>$request['xc-sign'],
        )));?>" data-multiple="<?php echo absint($this->data["multi"]);?>" data-placeholder="<?php echo esc_attr($this->data['placeholder']);?>" class="xc-search2 form-control <?php echo $this->data['multi']?'select2-multiple':'select2'?> <?php echo esc_attr(join(' ',array_unique($this->data['class']))  ); ?>" name="<?php echo $field?>" id="<?php echo $field?>"  style="<?php echo $this->getCustomerStyleHtml(); ?>" <?php echo  $this->data['disabled']?'disabled':''; ?>  <?php echo $this->getCustomAttributeHtml(  ); ?> data-sortable="true" data-allow_clear="true" >
            <?php if($items){
                foreach ($items as $item){
                    ?><option value="<?php echo esc_attr( $item['id']);?>"><?php echo $item['title']?></option><?php
                }
            }?>
        </select>

        <script type="text/javascript">
            (function($,undefined){
                let defaultValues = <?php echo maybe_json_encode($value)?>;
                $(document).bind('xc-on-select2-init',function(){
                    $('#<?php echo $field?>').val(defaultValues).trigger('change');

                    $('#<?php echo $field?>').change(function(){
                        let val = $('#<?php echo $field?>').val();

                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'keyup',
                            value:val
                        });
                    });
                });

                $(function(){
                    $(document).bind("handle_<?php echo $form_id?>_reset",function(e,form){
                        $('#<?php echo $field?>').val(defaultValues).trigger('change');
                    });
                    $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                        let val = $('#<?php echo $field?>').val();
                        if(!val){
                            form.<?php echo esc_attr($this->key)?> = null;
                            return;
                        }
                        form.<?php echo esc_attr($this->key)?> =  typeof val==="object"?JSON.stringify(val):val;
                    });

                    window.set_field_<?php echo $field?>_value = function(value){
                        $('#<?php echo $field?>').val(value).trigger('change');
                    };
                });
            })(jQuery);

            <?php if(!defined('XC_SEARCH2_SCRIPT')){
                  define('XC_SEARCH2_SCRIPT',true);
                ?>
                    jQuery( function( $ ) {
                        function getEnhancedSelectFormatString() {
                            return {
                                'language': {
                                    errorLoading: function() {
                                        return '加载失败！';
                                    },
                                    inputTooLong: function( args ) {
                                        let overChars = args.input.length - args.maximum;

                                        if ( 1 === overChars ) {
                                            return '最多输入一个字符！';
                                        }

                                        return '最多输入'+overChars+'个字符！';
                                    },
                                    inputTooShort: function( args ) {
                                        let remainingChars = args.minimum - args.input.length;

                                        if ( 1 === remainingChars ) {
                                            return '至少输入一个字符！';
                                        }

                                        return '至少输入'+remainingChars+'个字符！';
                                    },
                                    loadingMore: function() {
                                        return '正在加载更多...';
                                    },
                                    maximumSelected: function( args ) {
                                        if ( args.maximum === 1 ) {
                                            return '您最多只能选择一项！';
                                        }

                                        return '您最多只能选择'+args.maximum+'项';
                                    },
                                    noResults: function() {
                                        return '暂无内容';
                                    },
                                    searching: function() {
                                        return '搜索中...';
                                    }
                                }
                            };
                        }

                        $( document.body ).on( 'xc-enhanced-select-init2', function() {
                            // Ajax search boxes
                            let xc_search=function(filter){
                                $( filter ).filter( ':not(.enhanced)' ).each( function() {
                                    let custom_params = $( this ).data( 'custom_params' );
                                    if(custom_params&&typeof custom_params==='string'){
                                        custom_params = jQuery.parseJSON(custom_params);
                                    }

                                    if(!custom_params||typeof custom_params!=='object'){custom_params={};}

                                    let select2_args = {
                                        multiple: $( this ).data( 'multiple' )=='1',
                                        allowClear:  !!$(this).data('allow_clear'),
                                        placeholder: $( this ).data( 'placeholder' ),
                                        minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '0',
                                        escapeMarkup: function( m ) {
                                            return m;
                                        },
                                        ajax: {
                                            url:         '<?php echo url("module/web.Func/search2")?>',
                                            dataType:    'json',
                                            method:'post',
                                            delay:       250,
                                            data:        function( params ) {
                                                custom_params.keywords=params.term;
                                                return custom_params;
                                            },
                                            processResults: function( data ) {
                                                if ( data &&data.items) {
                                                    return {
                                                        results:  data.items
                                                    };
                                                }

                                                return {
                                                    results: []
                                                };
                                            },
                                            cache: true
                                        }
                                    };

                                    select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );
                                    $( this ).select2( select2_args ).addClass( 'enhanced' );
                                });
                            };

                            xc_search(':input.xc-search2');
                            $(document).trigger('xc-on-select2-init');
                        }).trigger( 'xc-enhanced-select-init2' );

                        $( 'html' ).on( 'click', function( event ) {
                            if ( this === event.target ) {
                                $( ':input.xc-search2' ).filter( '.select2-hidden-accessible' ).select2( 'close' );
                            }
                        });
                    });
            <?php
            }?>
        </script>
        <?php
    }
}