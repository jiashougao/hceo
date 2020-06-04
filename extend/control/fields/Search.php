<?php
namespace control\fields;

use think\Exception;
use think\Response;

class Search extends Base
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

        'dbName'=>'',//表名
        'multi'=>0,//是否多选
        'titleColumn'=>'name',//字段  标题
        'idColumn' =>'id',//字段 ID,
        //额外的过滤条件
        'filter'=>array(
            'state'=>'active'
        ),
        //对预览数据格式化
        'preview_item'=>null,
        'custom_attributes' => array ()
    );
    private function prepareData(){
        $this->data = apply_filters('form_field_search_data',$this->data);
    }

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

        return $value;
    }

    public function preview($strip_tags = false,$values=null){
        $this->prepareData();
        $value = $this->getValue(true);

        $items = array();
        foreach ($value as $id){
            if(empty($id)){continue;}
            $m = db_default($this->data['dbName'])->where($this->data['idColumn'],$id)->find();
            if(!$m){
                continue;
            }
            $items[]=array(
                $this->data['idColumn']=>$m[$this->data['idColumn']],
                $this->data['titleColumn']=>empty($m[$this->data['titleColumn']])?$m['id']:$m[$this->data['titleColumn']]
            );
        }

        if($strip_tags){
            $str ="";
            foreach ($items as $item){
                if($str){$str.=",";}
                $str.=$item[$this->data['titleColumn']];
            }

            return $str;
        }

        ob_start();
        ?>
        <ul style="list-style: none;padding:0;margin:0">
            <?php
            $preview_item = $this->get('preview_item');
            foreach ($items as $file){
                ?><li><div  class='form-control-textarea-preview'><?php echo $preview_item?$preview_item($file):$file[$this->data['titleColumn']]?></div></li><?php
            }?>
        </ul>
        <?php
        return ob_get_clean();
    }

    public function generateField($form_id) {
        $this->prepareData();
        $field = $this->getFieldKey ( $form_id );
        $request=array(
            'title'=>$this->data['titleColumn'],
            'id'=>$this->data['idColumn'],
            'db'=>$this->data['dbName'],
            'filter'=>maybe_json_encode($this->data['filter']),
        );

        ksort($request);
        reset($request);
        $request['xc-sign'] = md5(http_build_query($request).config('app.auth_salt'));

        $value = $this->getValue(true);
        if(!is_array($value)){
            $value=array();
        }

        $items = array();
        foreach ($value as $id){
            if(empty($id)){continue;}
            $m = db_default($this->data['dbName'])->where($this->data['idColumn'],$id)->find();
            if(!$m){
                continue;
            }
            $items[]=array(
                $this->data['idColumn']=>$m[$this->data['idColumn']],
                $this->data['titleColumn']=>empty($m[$this->data['titleColumn']])?$m['id']:$m[$this->data['titleColumn']]
            );
        }
        ?>
        <select data-custom_params="<?php echo esc_attr(maybe_json_encode(array(
            'dbName'=>$request['db'],
            'titleColumn'=>$request['title'],
            'idColumn'=>$request['id'],
            'filter'=>$request['filter'],
            'xc-sign'=>$request['xc-sign'],
        )));?>" data-multiple="<?php echo absint($this->data["multi"]);?>" data-placeholder="<?php echo esc_attr($this->data['placeholder']);?>" class="xc-search form-control <?php echo $this->data['multi']?'select2-multiple':'select2'?> <?php echo esc_attr(join(' ',array_unique($this->data['class']))  ); ?>" name="<?php echo $field?>" id="<?php echo $field?>"  style="<?php echo $this->getCustomerStyleHtml(); ?>" <?php echo  $this->data['disabled']?'disabled':''; ?>  <?php echo $this->getCustomAttributeHtml(  ); ?> data-sortable="true" data-allow_clear="true" >
            <?php if($items){
                foreach ($items as $item){
                    ?><option value="<?php echo esc_attr( $item[$this->data['idColumn']]);?>"><?php echo $item[$this->data['titleColumn']]?></option><?php
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
                        form.<?php echo esc_attr($this->key)?> = typeof val==="object"?JSON.stringify(val):val;
                    });

                    window.set_field_<?php echo $field?>_value = function(value){
                        $('#<?php echo $field?>').val(value).trigger('change');
                    };
                });
            })(jQuery);
        </script>
        <?php
    }
}