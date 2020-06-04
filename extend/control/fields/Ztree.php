<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/5/8
 * Time: 14:32
 */

namespace control\fields;


class Ztree extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'class' => array(),
        'style' => array(
            'width'=>'100%'
        ),
        'multi'=>0,
        'placeholder' => '',
        'level'=>0,
        'default'=>array(),
        'value'=>'',
        'options'=>[],
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
        if(!is_array($value)){
            $value=[null];
        }
        return $value;
    }

    public function getOptions(){
        if(!is_string($this->data['options'])&&is_callable($this->data['options'])){
            $this->data['options'] = call_user_func($this->data["options"]);
        }
        return maybe_json_decode($this->data['options']);
    }

    public function preview($strip_tags = false,$values=null){
        $value = $this->getValue(true);
        $options = $this->getOptions();
        $items = array();
        foreach ($value as $id){
            foreach ($options as $option){
                if($option['id']==$id){
                    $items[]=$option;
                    break;
                }
            }
        }

        if($strip_tags){
            $str ="";
            foreach ($items as $item){
                if($str){$str.=",";}
                $str.= $item['name'];
            }

            return $str;
        }

        ob_start();
        ?>
        <ul style="list-style: none;padding:0;margin:0">
            <?php foreach ($items as $item){
                ?><li><div  class='form-control-textarea-preview'><?php echo $item['name'];?></div></li><?php
            }?>
        </ul>
        <?php
        return ob_get_clean();
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );

        $value = $this->getValue(true);
        $options = $this->getOptions();
        $items = array();
        $title = "";
        foreach ($value as $id){
            foreach ($options as $option){
                if($option['id']==$id){
                    $items[]=$option;
                    if($title){
                        $title.=";";
                    }
                    $title.=$option['name'];
                    break;
                }
            }
        }
        if(!defined('XC_ZTREE_SCRIPT')){
            define('XC_ZTREE_SCRIPT',true);
            ?>  <script src="<?php echo mini_url('ztree.js')?>"></script>
            <link rel="stylesheet" href="<?php echo mini_url('ztree_v3.css')?>" />
            <?php
        }
        ?>

        <div style="position: relative!important;" id="<?php echo $field?>-container">
            <input type="hidden" id="<?php echo $field;?>" value="<?php echo esc_attr(json_encode($value))?>">
            <input type="text" class="form-control <?php echo esc_attr( join(' ',array_unique($this->data['class']))  ); ?>" id="<?php echo $field?>-view"  style="<?php echo $this->getCustomerStyleHtml(); ?>" value="<?php echo esc_attr($title);?>" placeholder="<?php echo esc_attr( $this->data['placeholder'] ); ?>" readonly  <?php echo $this->getCustomAttributeHtml(  ); ?> />
            <div style="position: absolute!important;z-index:999;" >
                <ul id="<?php echo $field?>-items" style="display:none;" class="ztree"></ul>
            </div>
        </div>

        <script type="text/javascript">
            (function($,undefined){
                var options = <?php echo json_encode($options)?>;
                var defaultValues =  <?php echo json_encode($value)?>;
                var defaultTitle = "<?php echo esc_attr($title);?>";
                var level = <?php echo absint($this->data['level'])?>;
                $(function(){
                    $(document).bind("handle_<?php echo $form_id?>_reset",function(e,form){
                        $('#<?php echo $field?>').val(JSON.stringify(defaultValues));
                        $('#<?php echo $field?>-view').val(defaultTitle);
                    });

                    $("#<?php echo $field?>-view").click(function(){
                        var value = $('#<?php echo $field?>').val();
                        var selected = [];

                        for(var index in options){
                            options[index].open=  false;
                        }

                        //让默认选中
                        if(value){
                            try{
                                selected = $.parseJSON(value);
                            }catch (e) {

                            }
                            for(var index in options){
                                options[index].checked = false;
                            }

                            if(selected&&selected.length){
                                for(var index in options){
                                    for(var d in selected){
                                        if(options[index].id==selected[d]){
                                            options[index].checked = true;
                                            options[index].open = true;
                                            var $selectNode = options[index];
                                            while ($selectNode&&$selectNode.parent!=0) {
                                                for(var p in options){
                                                    if($selectNode.parent==options[p].id){
                                                        options[p].open = true;
                                                        $selectNode = options[p];
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }


                        <?php if($this->data['multi']==0){ ?>
                                    for(var index in options){
                                        if(options[index].level!=level){
                                            options[index].nocheck=  true;
                                        }
                                    }
                        <?php } ?>

                        var config={
                            view: {
                                dblClickExpand: false
                            },
                            data: {
                                simpleData: {
                                    enable: true,
                                    idKey: "id",
                                    pIdKey: "parent",
                                    rootPId: 0
                                }
                            },
                            callback: {
                                onClick:function(e, tree_id, tree_node) {
                                    var checked = !tree_node.checked;
                                    if(tree_node.level==level){
                                        window.<?php echo $field?>_tree.checkNode(tree_node, checked, true, true);
                                    }

                                    window.<?php echo $field?>_tree.expandNode(tree_node, !tree_node.open, false,true);
                                },
                                onCheck: function(event, treeId, treeNode){
                                    if(treeNode.children){
                                        for (var i=0, l=treeNode.children.length; i < l; i++) {
                                            window.<?php echo $field?>_tree.checkNode(treeNode.children[i], treeNode.checked, true, true);
                                        }
                                    }

                                    if(treeNode.level!=level){
                                        return;
                                    }
                                    var value = $('#<?php echo $field?>').val();
                                    var selected = [];

                                    //让默认选中
                                    if(value) {
                                        try{
                                            selected = $.parseJSON(value);
                                        }catch (e) {

                                        }
                                    }
                                    var newSelected = [];
                                    <?php if($this->data['multi']==1){ ?>
                                    //treeNode.checked
                                    for(var index in selected){
                                        if(selected[index]!=treeNode.id){
                                            newSelected.push(selected[index]);
                                        }
                                    }
                                    <?php } ?>

                                    if(treeNode.checked){
                                        newSelected.push(treeNode.id);
                                    }

                                    var title = '';
                                    for(var index in newSelected){
                                        for(var p in options){
                                            if(options[p].id==newSelected[index]){
                                                if(title){
                                                    title+=';';
                                                }
                                                title+=options[p].name;
                                            }
                                        }
                                    }
                                    $('#<?php echo $field?>-view').val(title);
                                    $('#<?php echo $field?>').val(JSON.stringify(newSelected));
                                    $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                                        column:"<?php echo $field?>",
                                        event:'focus',
                                        value:JSON.stringify(newSelected)
                                    });
                                }
                            }
                        };

                        <?php if($this->data['multi']==1){ ?>
                        config.check = {
                            enable: true,
                            chkStyle: "checkbox",
                            chkboxType: { "Y" : "s", "N" : "s"  }
                        };
                        <?php
                        }else{
                        ?>
                        config.check = {
                            enable: true,
                            chkStyle: "radio",
                            radioType: "all"
                        };
                        <?php
                        }?>

                        window.<?php echo $field?>_tree = $.fn.zTree.init($("#<?php echo $field?>-items"), config, options);
                        $("#<?php echo $field?>-items").slideDown('fast');

                        $(document).off('click.<?php echo $field?>-ztree').on('click.<?php echo $field?>-ztree',function(e){
                            if (!(e.target.id === "<?php echo $field?>-container" || $(e.target).parents("#<?php echo $field?>-container").length > 0)) {
                                $("#<?php echo $field?>-items").fadeOut("fast");
                            }
                        });
                    });

                    $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                        var value = $('#<?php echo $field?>').val();
                        try{
                            if(value){
                                value = $.parseJSON(value);

                                //去除单一情况下的数组
                                <?php if($this->data['multi']==0){ ?>
                                        if(value.length===1){
                                            value = value[0];
                                        }
                                <?php } ?>
                            }
                        }catch (e) {

                        }
                        form.<?php echo esc_attr($this->key)?> = value;
                    });

                    window.set_field_<?php echo $field?>_value = function(value){
                        $('#<?php echo $field?>').val(value);
                    };
                });
            })(jQuery);
        </script>
        <?php
    }
}