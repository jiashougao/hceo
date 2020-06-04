<?php

namespace control\fields_bootstrap;

use control\fields\Base;
use control\ControlFBuilder;
use think\Response;

class Fbuilder extends Base
{
    public $default = array (
        'cel'=>4,//一行最多4个文本框
         //默认字段：不能删除，不能修改name
        'fixed_fields'=>array(),
        //默认字段：能删除，不能修改name
        'often_fields'=>array(),
        //声明可用的字段类型
        'components'=>array(),
        //声明禁用的字段类型
        'disabled_components'=>array(
            'fbuilder'
        ),
        //可引用的数据库
        'dbList'=>[],
        //能删除，能修改name
        'default' => array(),
        'title'=>null,
        'required'=>false,
        'value'=>''
    );

    /**
     * 表单组件，不显示内容
     * @param bool $strip_tags
     * @return null|string
     */
    public function preview($strip_tags = false,$values=null){
        return null;
    }

    public function generateField($form_id){
        $field = $this->getFieldKey($form_id);

        $fixedFields = !array_is_empty($this->data['fixed_fields'])?$this->data['fixed_fields']:array();
        $value =maybe_json_decode($this->getValue(),true);
        if(!$value||!is_array($value)){
            $value = array();
        }

        foreach ($fixedFields as $fixedKey=>$fixed){
          if(!isset($value[$fixedKey])){
              $value[$fixedKey] = $fixed;
          }
        }

        $control = new ControlFBuilder();
        $fields = $control->getComponents($this->data['components'],$this->data['disabled_components']);
        $componentList=array();
        foreach ($fields as $groupKey=>$group){
            foreach ($group['options'] as $k=>$option){
                $componentList[$k] = array(
                    'title'=>$option['title']
                );
            }
        }

        $cel = absint($this->data['cel']);
        static $IS_RENDER_STYLE;
        if(!$IS_RENDER_STYLE){
            $IS_RENDER_STYLE = true;
            ?>
            <link rel="stylesheet" href="<?php echo mini_url('form-builder.css');?>" />
            <?php
        }
        if(!defined('XC_UEDITOR_SCRIPT')){
            define('XC_UEDITOR_SCRIPT',true);

            ?>
            <script src="/static/plugins/ueditor/ueditor.config.js"></script>
            <script src="/static/plugins/ueditor/ueditor.all.js"></script>
            <script src="/static/plugins/ueditor/third-party/zeroclipboard/ZeroClipboard.js"></script>
            <?php
        }
        ?>

        <div class="form-group <?php echo esc_attr($this->key)?>">
            <label class="col-md-2 control-label">
                <?php echo $this->data['title'];?>
                <?php if($this->data['required']){
                    ?>
                    <span class="required"> * </span>
                    <?php
                }?>
            </label>
            <div class="col-md-10">
                <div class="sui-2-2-7 <?php echo esc_attr($this->key)?>" id="<?php echo $field;?>-wpform-container" style="max-width:760px;">
                    <div class="sui-wrap wpmudev-forminator-forminator-cform-wizard fui-builder-page">
                        <section id="<?php echo $field;?>-wpmudev-section" class="forminator-form-wizard">
                            <div id="<?php echo $field;?>-forminator-form-builder" class="sui-box">
                                <div class="sui-box-body">
                                    <div class="fui-builder">
                                        <div class="fui-form-builder">
                                            <div class="wpmudev-builder--form-wrappers" id="<?php echo $field;?>-wpform-items-container">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <aside id="<?php echo $field;?>-forminator-form-elements" class="hide fui-builder-sidebar fui-active" role="complementary">
                                <div class="fui-sidebar-wrapper fui-show">
                                    <div class="fui-sidebar-header">
                                        <h2 class="wpmudev-title">点击 &amp; 拖动字段</h2>
                                        <div class="sui-actions-right">
                                            <a href="javascript:void(0);" role="button" class="wpform-dialog-config-close sui-button sui-button-ghost">关闭</a>
                                        </div>
                                    </div>
                                    <div class="wpmudev-sidebar--section wpmudev-sort-fields ui-tabs ui-corner-all ui-widget-content">
                                        <ul class="wpmudev-sidebar--menu wpmudev-sort-fields--menu ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix" role="tablist">
                                            <?php
                                            $index = 0;
                                            if(count($this->data['often_fields'])){
                                                $index++;
                                                ?>
                                                <li class="wpmudev-menu--item ui-tabs-tab ui-corner-top ui-state-default ui-tab ui-tabs-active ui-state-active">
                                                    <a href="#tab-wpform-fields-often">常用字段</a>
                                                </li>
                                                <li class="wpmudev-menu--item ui-tabs-tab ui-corner-top ui-state-default ui-tab">|</li>
                                                <?php
                                            }

                                            foreach ($fields as $groupKey=>$group){
                                                ?>
                                                <li class="wpmudev-menu--item ui-tabs-tab ui-corner-top ui-state-default ui-tab <?php echo $index++===0?'ui-tabs-active ui-state-active':''?>">
                                                    <a href="#tab-wpform-fields-<?php echo esc_attr($groupKey)?>"><?php echo $group['title']?></a>
                                                </li>
                                                <?php
                                            }?>
                                        </ul>

                                        <?php
                                        $index = 0;
                                        if(count($this->data['often_fields'])){
                                            $index++;
                                            ?>
                                            <div id="tab-wpform-fields-often" class="wpmudev-sidebar--content wpmudev-sort-fields--list ui-tabs-panel ui-corner-bottom ui-widget-content">
                                                <ul class="wpmudev-list--options">
                                                    <?php foreach ($this->data['often_fields'] as $columnKey=>$column){
                                                        ?>
                                                        <li class="draggable-element draggable-name draggable-element-column" data-column="<?php echo esc_attr($columnKey)?>"><?php echo $column['title']?></li>
                                                        <?php
                                                    }?>
                                                </ul>
                                            </div>
                                            <?php
                                        }
                                        foreach ($fields as $groupKey=>$group){
                                            ?><div id="tab-wpform-fields-<?php echo esc_attr($groupKey)?>" class="wpmudev-sidebar--content wpmudev-sort-fields--list ui-tabs-panel ui-corner-bottom ui-widget-content <?php echo $index++===0?'':'hide'?>">
                                                <ul class="wpmudev-list--options">
                                                    <?php foreach ($group['options'] as $fieldKey=>$theField){
                                                        ?>
                                                        <li class="draggable-element draggable-name draggable-element-field" data-type="<?php echo esc_attr($fieldKey)?>"><?php echo $theField['title']?></li>
                                                        <?php
                                                    }?>
                                                </ul>
                                            </div><?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </aside>
                        </section>
                    </div>
                    <div id="<?php echo $field;?>-wpmudev-sidebar-settings" class="wpmudev-ui sui-wrap"></div>
                </div>
                <div id="<?php echo $field;?>-switch"  class="wpform-switcher d-flex justify-content-center align-items-center">
                    <i class="fa fa-pencil"></i>
                </div>
                <script type="text/javascript">
                (function($,undefined){
                    window.<?php echo $field?>View={
                        //表单字段数据
                        items:<?php echo maybe_json_encode($value);?>,
                        components:<?php echo maybe_json_encode($componentList)?>,
                        often_fields:<?php echo maybe_json_encode($this->data['often_fields'])?>,
                        fixed_fields:<?php echo maybe_json_encode($fixedFields)?>,
                        //所有可接收拖动组件的容器
                        drops:[],
                        //当前已选中的容器
                        drop:null,
                        //获取field对应的data
                        getFieldData:function(field){
                            let items = this.prepareItems(false);
                            if(!items||!_.size(items)){
                                return false;
                            }

                            if(typeof items[field]!=='undefined'){
                                return items[field];
                            }

                            for(let index in items){
                                let item = items[index];
                                if(!item.type){
                                    item.type='text';
                                }
                                if(item.type==='row'&&item.items){
                                    if(typeof item.items[field]!=='undefined'){
                                        return item.items[field];
                                    }
                                }
                            }
                            return false;
                        },
                        //去除row下单行数据的情况
                        prepareItems:function(clearName){
                            let newItems = {};
                            if(!this.items){
                                return newItems;
                            }

                            for(let index in this.items){
                                let item = this.items[index];
                                if(typeof this.fixed_fields[index]!='undefined'){
                                    item.columnNameFixed = true;
                                }
                                if(!item.type){
                                    item.type='text';
                                }

                                if(item.type==='row'&&!item.items){
                                    continue;
                                }

                                if(item.type==='row'&&!_.size(item.items)){
                                    continue;
                                }
                                if(item.type!=='row'&&typeof window.formBuilder['generate_'+item.type+'_field']!=='function'){
                                    continue;
                                }
                                if(item.type==='row'){
                                    let newChildren = {};
                                    for(let sub in item.items){
                                        let subItem = item.items[sub];
                                        if(clearName){
                                            delete subItem.name;
                                        }else{
                                            subItem.name = sub;
                                        }
                                        if(!subItem.type){
                                            subItem.type='text';
                                        }
                                        if(subItem.type==='row'||typeof window.formBuilder['generate_'+subItem.type+'_field']!=='function'){
                                            continue;
                                        }
                                        newChildren[sub] = subItem;
                                    }
                                    item.items = newChildren;
                                }
                                //去掉row 只有一个元素，那么去掉row
                                if(item.type==='row'&&_.size(item.items)===1){
                                    for(let sub in item.items){
                                        let subItem = item.items[sub];
                                        if(typeof this.fixed_fields[sub]!='undefined'){
                                            subItem.columnNameFixed = true;
                                        }
                                        newItems[sub] = subItem;
                                    }
                                    continue;
                                }

                                //绑定name值
                                if(clearName){
                                    delete item.name;
                                }else{
                                    item.name = index;
                                }

                                newItems[index] = item;
                            }

                            this.items = newItems;
                            return newItems;
                        },
                        oftenFieldsInit:function(){
                            if(!this.often_fields){
                                return;
                            }

                            for(let index in this.often_fields){
                                this.often_fields[index].columnNameFixed = true;
                            }
                        },
                        //初始化表单的显示
                        dropsInit:function(){
                            let self = this;
                            //初始化拖动容器
                            $('#<?php echo $field;?>-wpform-items-container .forminator-drop').each(function(){
                                let $this = $(this);
                                let offset = $this.offset();
                                let height = $this.height();
                                let width = $this.width();
                                self.drops.push({
                                    dom:$this,
                                    left:offset.left,
                                    top:offset.top,
                                    bottom:offset.top+(height<20?20:height),
                                    right:offset.left+(width<20?20:width)
                                });
                            });
                        },
                        dataInit:function() {
                            let self = this;
                            self.drops = [];
                            let $container = $('#<?php echo $field;?>-wpform-items-container').empty();
                            let html = '';

                            let index = 0;
                            let items = this.prepareItems(false);
                            if(items&&_.size(items)) {
                                for (index in items) {
                                    let item = items[index];
                                    if(!item.type){
                                        item.type='text';
                                    }

                                    html += '<div data-index="'+index+'" data-direction="top" class="forminator-drop forminator-drop-full" style="width: 699px;"></div>';

                                    if (item.type === 'row' && item.items) {
                                        html+='<div class="fui-form-builder--row ui-draggable ui-draggable-handle">';
                                        for(let childIndex in item.items){
                                            let child = item.items[childIndex];
                                            if(self.often_fields&&typeof self.often_fields[child.name]!=='undefined'){
                                                child.columnNameFixed = true;
                                            }
                                            if(!child.type){
                                                child.type='text';
                                            }
                                            if(typeof window.formBuilder['generate_'+child.type+'_field']!=='function'){
                                                continue;
                                            }
                                            html+='<div data-index="'+index+'" data-direction="before" data-sub-index="'+childIndex+'" class="forminator-drop forminator-drop-side-before" style="height: 78px;"></div>';

                                            html += window.formBuilder['generate_'+child.type+'_field'](childIndex,child,_.size(item.items));

                                            html+='<div data-index="'+index+'" data-direction="after" data-sub-index="'+childIndex+'" class="forminator-drop forminator-drop-side-after" style="height: 78px;"></div>';
                                        }

                                        html+="</div>";
                                    }else{
                                        if(typeof window.formBuilder['generate_'+item.type+'_field']!=='function'){
                                            continue;
                                        }
                                        if(self.often_fields&&typeof self.often_fields[item.name]!=='undefined'){
                                            item.columnNameFixed = true;
                                        }
                                        html += '<div class="fui-form-builder--row ui-draggable ui-draggable-handle">' +
                                                    '<div data-index="'+index+'" data-direction="before" class="forminator-drop forminator-drop-side-before" style="height: 93px;"></div>' ;

                                                     html += window.formBuilder['generate_'+item.type+'_field'](index,item,1);

                                                     html += '<div data-index="'+index+'" data-direction="after" class="forminator-drop forminator-drop-side-after" style="height: 93px;"></div>' +
                                                '</div>';
                                    }
                                    html += '<div data-index="'+index+'" data-direction="bottom" class="forminator-drop forminator-drop-full" style="width: 699px;"></div>';
                                }
                            }else{
                                html += '<div class="d-flex align-items-center justify-content-center fui-form-builder--row ui-draggable ui-draggable-handle" style="border:dashed 2px #e5e5e5;width:100%;height:65px;border-radius:30px;">' +
                                            '<label class="forminator-drop forminator-drop-empty">拖拽右侧标签到此处</label>' +
                                        '</div>';
                            }

                            $container.html(html);

                            //初始化已拖入的字段的排序
                            $("#<?php echo $field;?>-wpmudev-section .form-editor-field").draggable({
                                //允许draggable被拖拽到指定的sortables中，如果使用此选项helper属性必须设置成clone才能正常工作。
                                connectToSortable: "#<?php echo $field;?>-wpmudev-section .wpmudev-builder--form-wrappers",
                                //拖拽元素时的显示方式。（如果是函数，必须返回值是一个DOM元素）可选值：'original', 'clone', Function
                                helper: "original",
                                //当元素拖拽结束后，元素回到原来的位置。
                                revertDuration:200,
                                revert: function(event, ui){
                                    return !self.drop;
                                },
                                //防止在指定的对象上开始拖动
                                cancel: false,
                                zIndex:99999,
                                //The element passed to or selected by the appendTo option will be used as the draggable helper's container during dragging. By default, the helper is appended to the same container as the draggable.
                                appendTo: "#<?php echo $field;?>-wpform-container",
                                //强制draggable只允许在指定元素或区域的范围内移动，可选值：'parent', 'document', 'window', [x1, y1, x2, y2].
                                containment: 'document',
                                //当鼠标开始拖拽时，触发此事件。
                                start: function(event, ui){
                                    self.drop = null;
                                    ui.helper.addClass("element-dragging");
                                    self.dropsInit();
                                    return true;
                                },
                                stop:function(event, ui){
                                    let fieldKey = ui.helper.data('field');
                                    let data = self.getFieldData(fieldKey);
                                    let drop = self.drop;
                                    if(!drop){
                                        return;
                                    }
                                    $(".forminator-drop-use").removeClass("forminator-drop-use");
                                    let index = drop.dom.data('index');
                                    let direction = drop.dom.data('direction');
                                    let subIndex = drop.dom.data('sub-index');
                                    let items = self.items;
                                    if(!items||!_.size(items)){
                                        self.items={};
                                        self.items[fieldKey] = data;
                                        self.dataInit();
                                        return;
                                    }

                                    let newItems = {};
                                    for(let pop in items){
                                        let item = items[pop];
                                        if(!item.type){
                                            item.type='text';
                                        }

                                        if(direction==='top'&&index===pop){
                                            newItems[fieldKey] =data;

                                            let child = items[pop];
                                            if(child.type==='row'&&child.items){
                                                delete child.items[fieldKey];
                                            }
                                            if(pop!==fieldKey){
                                                newItems[pop] = child;
                                            }

                                            continue;
                                        }

                                        if(direction==='bottom'&&index===pop){
                                            let child = items[pop];
                                            if(child.type==='row'&&child.items){
                                                delete child.items[fieldKey];
                                            }
                                            if(pop!==fieldKey){
                                                newItems[pop] = child;
                                            }

                                            newItems[fieldKey] =data;
                                            continue;
                                        }

                                        if(direction==='before'&&index===pop){
                                            //如果是row 那么直接插入数据
                                            if(item.type==='row'){
                                                if(!item.items){
                                                    item.items={};
                                                    newItems[fieldKey] = data;
                                                    if(pop!==fieldKey)
                                                    newItems[pop] = item;
                                                    continue;
                                                }

                                                let newSubItems = {};
                                                for(let sub in item.items){
                                                    if(sub===subIndex&&_.size(item.items)<<?php echo $cel;?>){
                                                        newSubItems[fieldKey] = data;
                                                    }
                                                    if(sub!==fieldKey)
                                                    newSubItems[sub] = item.items[sub];
                                                }
                                                item.items = newSubItems;
                                                newItems[index] = item;
                                                continue;
                                            }

                                            //如果不是row，而是普通字段，那么构造row字段
                                            let newItem={
                                                'type':'row',
                                                'items':{}
                                            };

                                            newItem['items'][fieldKey] = data;
                                            if(pop!==fieldKey)
                                            newItem['items'][pop] = item;

                                            newItems['group_'+(new Date()).getTime()] = newItem;
                                            continue;
                                        }

                                        if(direction==='after'&&index===pop){
                                            //如果是row 那么直接插入数据
                                            if(item.type==='row'){
                                                if(!item.items){
                                                    item.items={};
                                                    if(pop!==fieldKey)
                                                    newItems[pop] = item;
                                                    newItems[fieldKey] = data;
                                                    continue;
                                                }

                                                let newSubItems = {};
                                                for(let sub in item.items){
                                                    if(sub!==fieldKey)
                                                    newSubItems[sub] = item.items[sub];
                                                    if(sub===subIndex&&_.size(item.items)<<?php echo $cel;?>){
                                                        newSubItems[fieldKey] = data;
                                                    }
                                                }
                                                item.items = newSubItems;
                                                newItems[index] = item;
                                                continue;
                                            }

                                            //如果不是row，而是普通字段，那么构造row字段
                                            let newItem={
                                                'type':'row',
                                                'items':{}
                                            };
                                            if(pop!==fieldKey)
                                            newItem['items'][pop] = item;
                                            newItem['items'][fieldKey] = data;
                                            newItems['group_'+(new Date()).getTime()] = newItem;
                                            continue;
                                        }

                                        if(item.type==='row'){
                                            delete item.items[fieldKey];
                                        }

                                        if(pop!==fieldKey){
                                            newItems[pop] = item;
                                        }
                                    }

                                    self.items = newItems;
                                    self.dataInit();
                                },
                                drag:function(t, e){
                                    self.drop =  _.find(self.drops, function (d) {
                                        return t.pageY > (d.top-20)
                                            && t.pageY < (d.bottom+20)
                                            && t.pageX > (d.left-20)
                                            && t.pageX < (d.right+20)
                                    });

                                    $(".forminator-drop-use").removeClass("forminator-drop-use");
                                    if(self.drop){
                                        self.drop.dom.addClass("forminator-drop-use");
                                    }
                                }
                            });

                            //初始化已拖入的字段，配置菜单的弹出功能
                            $('#<?php echo $field;?>-wpmudev-section .form-editor-field').bind('click',function(){
                                let fieldType = $(this).data('type');
                                let typeConfig = self.components[fieldType];
                                let fieldKey = $(this).data('field');
                                let data = self.getFieldData(fieldKey);
                                if(typeof  window.formBuilder["get_"+fieldType+"_config"]!=='function'){
                                    return false;
                                }
                                $('#<?php echo $field;?>-switch').addClass('hide');
                                $('#<?php echo $field;?>-forminator-form-elements').addClass('hide');
                                let config =  window.formBuilder["get_"+fieldType+"_config"](fieldKey,data);
                                let html='<div class="fui-builder-sidebar fui-active">' +
                                            '<div class="fui-sidebar-wrapper fui-show">' +
                                                '<div class="fui-sidebar-header">' +
                                                    '<h2 style="font-size:15px;" class="d-flex align-items-center"><i class="icon-settings" aria-hidden="true"></i> '+typeConfig.title+'</h2>' +
                                                    '<div class="sui-actions-right wpmudev-breadcrumb--back">' +
                                                        '<a href="javascript:void(0);" role="button" class="wpform-dialog-config-close sui-button sui-button-ghost">关闭</a>' +
                                                    '</div>' +
                                                '</div>' +
                                                '<div class="wpmudev-sidebar--section ui-tabs ui-corner-all ui-widget-content">' +
                                                    '<ul class="wpmudev-sidebar--menu ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix" role="tablist">';
                                                       let groupIndex = 0;
                                                       for(let groupKey in config){
                                                           let group = config[groupKey];
                                                           html+='<li class="wpmudev-menu--item settings-general ui-tabs-tab ui-corner-top ui-state-default ui-tab '+(groupIndex++===0?'ui-tabs-active ui-state-active':'')+'">' +
                                                                    '<a href="#<?php echo $field?>wpmudev-config-'+groupKey+'">'+group.title+'</a>' +
                                                                 '</li>';
                                                       }
                                                    html+= '</ul>';

                                                groupIndex=0;
                                                for(let groupKey in config){
                                                    let group = config[groupKey];
                                                    html+='<div id="<?php echo $field?>wpmudev-config-'+groupKey+'" class="wpmudev-sidebar--content ui-tabs-panel ui-corner-bottom ui-widget-content '+(groupIndex++===0?'':'hide')+'" >';
                                                        html+='<div class="wpmudev-options--wrap">'
                                                            for(let optionKey in group.options){
                                                                let value = typeof data[optionKey]!=='undefined'?data[optionKey]:'';
                                                                let option = group.options[optionKey];
                                                                html+=window.formBuilder.getFieldTemplate(optionKey,option,value);
                                                            }
                                                        html+='</div>';
                                                    html+='</div>';
                                                }
                                            html+='</div>' +
                                            '<div class="fui-sidebar-footer">' +
                                                '<div class="wpmudev-footer--buttons">' +
                                                    '<button type="button" class="wpform-dialog-config-clone sui-button sui-button-ghost wpmudev-clone-field">克隆</button>' +
                                                    '<button type="button" class="wpform-dialog-config-save sui-button sui-button-primary wpmudev-done-field">完成</button>' +
                                                '</div>';
                                                    //如果系统内定字段，那么不允许删除
                                                    if(typeof self.fixed_fields[fieldKey]==='undefined'||typeof self.fixed_fields[fieldKey]==='function'){
                                                        html+='<p class="wpmudev-footer--link"><a href="javascript:void(0);" class="wpform-dialog-config-remove wpmudev-delete-field">删除字段</a></p>';
                                                    }
                                            html+= '</div>' +
                                        '</div>' +
                                    '</div>';

                                let onDialogConfigClose=function(){
                                    $('#<?php echo $field;?>-switch').removeClass('hide');
                                    $('#<?php echo $field;?>-wpmudev-sidebar-settings').empty();
                                }  ;

                                $('#<?php echo $field;?>-wpmudev-sidebar-settings').html(html);
                                self.menuInit('#<?php echo $field;?>-wpmudev-sidebar-settings');
                                //关闭窗口
                                $('#<?php echo $field;?>-wpmudev-sidebar-settings .wpform-dialog-config-close').click(function(){
                                    onDialogConfigClose();
                                });
                                //克隆
                                $('#<?php echo $field;?>-wpmudev-sidebar-settings .wpform-dialog-config-clone').click(function(){
                                    let items = self.prepareItems(false);
                                    let newItems = {};
                                    for(let index in items){
                                        let item = items[index];
                                        if(item.type==='row'){
                                            let newSubItems = {};
                                            for(let sub in item.items){
                                                newSubItems[sub] = item.items[sub];
                                                if(sub===fieldKey){
                                                    newSubItems['field_'+(new Date()).getTime()] = item.items[sub];
                                                }
                                            }
                                            item.items = newSubItems;
                                            newItems[index]  = item;
                                            continue;
                                        }

                                        newItems[index]  = item;
                                        if(index===fieldKey){
                                            newItems['field_'+(new Date()).getTime()] = item;
                                        }
                                    }
                                    self.items = newItems;
                                    self.dataInit();
                                    onDialogConfigClose();
                                });
                                //保存
                                $('#<?php echo $field;?>-wpmudev-sidebar-settings .wpform-dialog-config-save').click(function(){
                                   let data = {
                                       type:fieldType,
                                       name:fieldKey
                                   };
                                    for(let groupKey in config){
                                       for(let k in config[groupKey]['options']){
                                           let op = config[groupKey]['options'][k];
                                           data[k] =  window.formBuilder.getFieldData(k,op);
                                       }
                                    }
                                    let newFieldKey = data.name;//.toLocaleLowerCase();
                                    if(!newFieldKey){
                                        htmlhelper.dialog.error("字段名不能为空！");
                                        return false;
                                    }
                                    let rex = /^[a-z][a-z\d_]*$/;
                                    if(!rex.test(newFieldKey.toLocaleLowerCase())){
                                        htmlhelper.dialog.error("字段名格式错误！");
                                        return false;
                                    }

                                    if(newFieldKey!==fieldKey&&self.getFieldData(newFieldKey)){
                                        htmlhelper.dialog.error("字段名已存在！");
                                        return false;
                                    }
                                    let items = self.prepareItems(false);
                                    let newItems = {};
                                    for(let index in items){
                                        let item = items[index];
                                        if(item.type==='row'){
                                            let newSubItems = {};
                                            for(let sub in item.items){
                                                if(sub===fieldKey){
                                                    newSubItems[newFieldKey] = data;
                                                    continue;
                                                }
                                                newSubItems[sub] = item.items[sub];
                                            }
                                            item.items = newSubItems;
                                            newItems[index]  = item;
                                            continue;
                                        }

                                        if(index===fieldKey){
                                            newItems[newFieldKey]  = data;
                                            continue;
                                        }
                                        newItems[index]  = item;
                                    }
                                    self.items = newItems;
                                    self.dataInit();
                                    onDialogConfigClose();
                                });
                                //删除字段
                                $('#<?php echo $field;?>-wpmudev-sidebar-settings .wpform-dialog-config-remove').click(function(){
                                    htmlhelper.dialog.confirm({
                                        title:'确认删除当前字段吗？',
                                        confirm:function(){
                                            let items = self.prepareItems(false);
                                            let newItems = {};
                                            for(let index in items){
                                                let item = items[index];
                                                if(item.type==='row'){
                                                    let newSubItems = {};
                                                    for(let sub in item.items){
                                                        if(sub===fieldKey){
                                                            continue;
                                                        }
                                                        newSubItems[sub] = item.items[sub];
                                                    }
                                                    item.items = newSubItems;
                                                    newItems[index]  = item;
                                                    continue;
                                                }

                                                if(index===fieldKey){
                                                    continue;
                                                }
                                                newItems[index]  = item;
                                            }
                                            self.items = newItems;
                                            self.dataInit();
                                            onDialogConfigClose();
                                        }
                                    });
                                });
                            });

                            $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                                column:"<?php echo $field?>",
                                event:'keyup',
                                value:JSON.stringify(window.<?php echo $field?>View.prepareItems(true))
                            });
                        },
                        //初始化右侧拖动按钮的拖动效果
                        dragInit:function(){
                            let self = this;
                            $("#<?php echo $field;?>-wpmudev-section .draggable-element-field").draggable({
                                //允许draggable被拖拽到指定的sortables中，如果使用此选项helper属性必须设置成clone才能正常工作。
                                connectToSortable: "#<?php echo $field;?>-wpmudev-section .wpmudev-builder--form-wrappers",
                                //拖拽元素时的显示方式。（如果是函数，必须返回值是一个DOM元素）可选值：'original', 'clone', Function
                                helper: "clone",
                                //当元素拖拽结束后，元素回到原来的位置。
                                revertDuration:200,
                                revert: function(event, ui){
                                    return !self.drop;
                                },
                                //防止在指定的对象上开始拖动
                                cancel: false,
                                zIndex:99999,
                                //The element passed to or selected by the appendTo option will be used as the draggable helper's container during dragging. By default, the helper is appended to the same container as the draggable.
                                appendTo: "#<?php echo $field;?>-wpform-container",
                                //强制draggable只允许在指定元素或区域的范围内移动，可选值：'parent', 'document', 'window', [x1, y1, x2, y2].
                                containment: 'document',
                                //当鼠标开始拖拽时，触发此事件。
                                start: function(event, ui){
                                    self.drop = null;
                                    ui.helper.addClass("element-dragging");
                                    self.dropsInit();
                                    return true;
                                },
                                stop:function(event, ui){
                                    let fieldType = ui.helper.data('type');
                                    let drop =  self.drop;
                                    if(!drop){
                                        return;
                                    }
                                    $(".forminator-drop-use").removeClass("forminator-drop-use");
                                    let index = drop.dom.data('index');
                                    let direction = drop.dom.data('direction');
                                    let subIndex = drop.dom.data('sub-index');
                                    let items = self.items;
                                    if(!items||!_.size(items)){
                                        self.items={};
                                        self.items['field_'+(new Date()).getTime()] = {
                                            'type':fieldType,
                                            'title':'未命名'
                                        };
                                        self.dataInit();
                                        return;
                                    }

                                    let newItems = {};
                                    for(let pop in items){
                                        let item = items[pop];
                                        if(!item.type){
                                            item.type='text';
                                        }

                                        if(direction==='top'&&index===pop){
                                            newItems['field_'+(new Date()).getTime()] = {
                                                'type':fieldType,
                                                'title':'未命名'
                                            };

                                            newItems[pop] = items[pop];
                                            continue;
                                        }

                                        if(direction==='bottom'&&index===pop){
                                            newItems[pop] = items[pop];
                                            newItems['field_'+(new Date()).getTime()] = {
                                                'type':fieldType,
                                                'title':'未命名'
                                            };
                                            continue;
                                        }

                                        if(direction==='before'&&index===pop){
                                            //如果是row 那么直接插入数据
                                            if(item.type==='row'){
                                                if(!item.items){
                                                    item.items={};
                                                    newItems['field_'+(new Date()).getTime()] = {
                                                        'type':fieldType,
                                                        'title':'未命名'
                                                    };
                                                    newItems[pop] = item;
                                                    continue;
                                                }

                                                let newSubItems = {};
                                                for(let sub in item.items){
                                                    if(sub===subIndex&&_.size(item.items)<<?php echo $cel;?>){
                                                        newSubItems['field_'+(new Date()).getTime()] = {
                                                            'type':fieldType,
                                                            'title':'未命名'
                                                        };
                                                    }
                                                    newSubItems[sub] = item.items[sub];
                                                }
                                                item.items = newSubItems;
                                                newItems[index] = item;
                                                continue;
                                            }

                                            //如果不是row，而是普通字段，那么构造row字段
                                            let newItem={
                                                'type':'row',
                                                'items':{}
                                            };
                                            newItem['items']['field_'+(new Date()).getTime()] = {
                                                'type':fieldType,
                                                'title':'未命名'
                                            };
                                            newItem['items'][pop] = item;
                                            newItems['group_'+(new Date()).getTime()] = newItem;
                                            continue;
                                        }

                                        if(direction==='after'&&index===pop){
                                            //如果是row 那么直接插入数据
                                            if(item.type==='row'){
                                                if(!item.items){
                                                    item.items={};
                                                    newItems[pop] = item;
                                                    newItems['field_'+(new Date()).getTime()] = {
                                                        'type':fieldType,
                                                        'title':'未命名'
                                                    };
                                                    continue;
                                                }

                                                let newSubItems = {};
                                                for(let sub in item.items){
                                                    newSubItems[sub] = item.items[sub];
                                                    if(sub===subIndex&&_.size(item.items)<<?php echo $cel;?>){
                                                        newSubItems['field_'+(new Date()).getTime()] = {
                                                            'type':fieldType,
                                                            'title':'未命名'
                                                        };
                                                    }
                                                }
                                                item.items = newSubItems;
                                                newItems[index] = item;
                                                continue;
                                            }

                                            //如果不是row，而是普通字段，那么构造row字段
                                            let newItem={
                                                'type':'row',
                                                'items':{}
                                            };
                                            newItem['items'][pop] = item;
                                            newItem['items']['field_'+(new Date()).getTime()] = {
                                                'type':fieldType,
                                                'title':'未命名'
                                            };
                                            newItems['group_'+(new Date()).getTime()] = newItem;
                                            continue;
                                        }

                                        newItems[pop] = item;
                                    }

                                    self.items = newItems;
                                    self.dataInit();
                                },
                                drag:function(t, e){
                                    self.drop =  _.find(self.drops, function (d) {
                                        return t.pageY > (d.top-20)
                                            && t.pageY < (d.bottom+20)
                                            && t.pageX > (d.left-20)
                                            && t.pageX < (d.right+20)
                                    });

                                    $(".forminator-drop-use").removeClass("forminator-drop-use");
                                    if(self.drop){
                                        self.drop.dom.addClass("forminator-drop-use");
                                    }
                                }
                            });

                            $("#<?php echo $field;?>-wpmudev-section .draggable-element-column").draggable({
                                //允许draggable被拖拽到指定的sortables中，如果使用此选项helper属性必须设置成clone才能正常工作。
                                connectToSortable: "#<?php echo $field;?>-wpmudev-section .wpmudev-builder--form-wrappers",
                                //拖拽元素时的显示方式。（如果是函数，必须返回值是一个DOM元素）可选值：'original', 'clone', Function
                                helper: "clone",
                                //当元素拖拽结束后，元素回到原来的位置。
                                revertDuration:200,
                                revert: function(event, ui){
                                    return !self.drop;
                                },
                                //防止在指定的对象上开始拖动
                                cancel: false,
                                zIndex:99999,
                                //The element passed to or selected by the appendTo option will be used as the draggable helper's container during dragging. By default, the helper is appended to the same container as the draggable.
                                appendTo: "#<?php echo $field;?>-wpform-container",
                                //强制draggable只允许在指定元素或区域的范围内移动，可选值：'parent', 'document', 'window', [x1, y1, x2, y2].
                                containment: 'document',
                                //当鼠标开始拖拽时，触发此事件。
                                start: function(event, ui){
                                    self.drop = null;
                                    ui.helper.addClass("element-dragging");
                                    self.dropsInit();
                                    return true;
                                },
                                stop:function(event, ui){
                                    let columnKey = ui.helper.data('column');
                                    let isColumnKeyExists = self.getFieldData(columnKey);
                                    if(isColumnKeyExists){
                                        alert('已存在相同字段name('+columnKey+')!');
                                        return;
                                    }
                                    let fieldType = self.often_fields[columnKey].type;

                                    if(!fieldType){fieldType="text";}
                                    let drop =  self.drop;
                                    if(!drop){
                                        return;
                                    }

                                    $(".forminator-drop-use").removeClass("forminator-drop-use");
                                    let index = drop.dom.data('index');
                                    let direction = drop.dom.data('direction');
                                    let subIndex = drop.dom.data('sub-index');
                                    let items = self.items;
                                    if(!items||!_.size(items)){
                                        self.items={};
                                        self.items[isColumnKeyExists?(columnKey+'_'+(new Date()).getTime()):columnKey] = self.often_fields[columnKey];
                                        self.dataInit();
                                        return;
                                    }

                                    let newItems = {};
                                    for(let pop in items){
                                        let item = items[pop];
                                        if(!item.type){
                                            item.type='text';
                                        }

                                        if(direction==='top'&&index===pop){
                                            newItems[isColumnKeyExists?(columnKey+'_'+(new Date()).getTime()):columnKey] = self.often_fields[columnKey];

                                            newItems[pop] = items[pop];
                                            continue;
                                        }

                                        if(direction==='bottom'&&index===pop){
                                            newItems[pop] = items[pop];
                                            newItems[isColumnKeyExists?(columnKey+'_'+(new Date()).getTime()):columnKey] = self.often_fields[columnKey];

                                            continue;
                                        }

                                        if(direction==='before'&&index===pop){
                                            //如果是row 那么直接插入数据
                                            if(item.type==='row'){
                                                if(!item.items){
                                                    item.items={};
                                                    newItems[isColumnKeyExists?(columnKey+'_'+(new Date()).getTime()):columnKey] = self.often_fields[columnKey];

                                                    newItems[pop] = item;
                                                    continue;
                                                }

                                                let newSubItems = {};
                                                for(let sub in item.items){
                                                    if(sub===subIndex&&_.size(item.items)<<?php echo $cel;?>){
                                                        newSubItems[isColumnKeyExists?(columnKey+'_'+(new Date()).getTime()):columnKey] = self.often_fields[columnKey];
                                                    }
                                                    newSubItems[sub] = item.items[sub];
                                                }
                                                item.items = newSubItems;
                                                newItems[index] = item;
                                                continue;
                                            }

                                            //如果不是row，而是普通字段，那么构造row字段
                                            let newItem={
                                                'type':'row',
                                                'items':{}
                                            };
                                            newItem[isColumnKeyExists?(columnKey+'_'+(new Date()).getTime()):columnKey] = self.often_fields[columnKey];

                                            newItem['items'][pop] = item;
                                            newItems['group_'+(new Date()).getTime()] = newItem;
                                            continue;
                                        }

                                        if(direction==='after'&&index===pop){
                                            //如果是row 那么直接插入数据
                                            if(item.type==='row'){
                                                if(!item.items){
                                                    item.items={};
                                                    newItems[pop] = item;
                                                    newItems[isColumnKeyExists?(columnKey+'_'+(new Date()).getTime()):columnKey] = self.often_fields[columnKey];

                                                    continue;
                                                }

                                                let newSubItems = {};
                                                for(let sub in item.items){
                                                    newSubItems[sub] = item.items[sub];
                                                    if(sub===subIndex&&_.size(item.items)<<?php echo $cel;?>){
                                                        newSubItems[isColumnKeyExists?(columnKey+'_'+(new Date()).getTime()):columnKey] = self.often_fields[columnKey];

                                                    }
                                                }
                                                item.items = newSubItems;
                                                newItems[index] = item;
                                                continue;
                                            }

                                            //如果不是row，而是普通字段，那么构造row字段
                                            let newItem={
                                                'type':'row',
                                                'items':{}
                                            };
                                            newItem['items'][pop] = item;
                                            newItem['items'][isColumnKeyExists?(columnKey+'_'+(new Date()).getTime()):columnKey] = self.often_fields[columnKey];
                                            newItems['group_'+(new Date()).getTime()] = newItem;
                                            continue;
                                        }

                                        newItems[pop] = item;
                                    }

                                    self.items = newItems;
                                    self.dataInit();
                                },
                                drag:function(t, e){
                                    self.drop =  _.find(self.drops, function (d) {
                                        return t.pageY > (d.top-20)
                                            && t.pageY < (d.bottom+20)
                                            && t.pageX > (d.left-20)
                                            && t.pageX < (d.right+20)
                                    });

                                    $(".forminator-drop-use").removeClass("forminator-drop-use");
                                    if(self.drop){
                                        self.drop.dom.addClass("forminator-drop-use");
                                    }
                                }
                            });
                        },
                        //右侧菜单tab切换效果
                        menuInit:function(group){
                            $(group+' .wpmudev-sidebar--menu .wpmudev-menu--item a').bind('click',function(e){
                                $(group+' .wpmudev-sidebar--menu .wpmudev-menu--item.ui-tabs-active').removeClass('ui-tabs-active ui-state-active');
                                $(this).parent('li').addClass('ui-tabs-active ui-state-active');
                                $(group+' .wpmudev-sidebar--content').addClass('hide');
                                $(group+' '+$(this).attr('href')).removeClass('hide');
                                e.stopPropagation();
                                return false;
                            });
                        }
                    };

                    $('#<?php echo $field;?>-forminator-form-elements .wpform-dialog-config-close').click(function(){
                        $('#<?php echo $field;?>-switch').removeClass('hide');
                        $("#<?php echo $field;?>-forminator-form-elements").addClass('hide');
                    });
                    $('#<?php echo $field;?>-switch').click(function(){
                        $('#<?php echo $field;?>-forminator-form-elements').removeClass('hide');
                        $(this).addClass('hide');
                    });

                    $(document).bind("handle_form_editor_init",function(){
                        window.<?php echo $field?>View.oftenFieldsInit();
                        window.<?php echo $field?>View.menuInit('#<?php echo $field;?>-forminator-form-elements');
                        window.<?php echo $field?>View.dataInit();
                        window.<?php echo $field?>View.dragInit();
                    });

                    $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                        form.<?php echo esc_attr($this->key)?> = JSON.stringify(window.<?php echo $field?>View.prepareItems(true));
                    });
                })(jQuery);
            </script>
            </div>
        </div>
        <?php

        static $IS_RENDER_SCRIPTS;
        if(!$IS_RENDER_SCRIPTS){
            $IS_RENDER_SCRIPTS = true;
           echo $this->generateScript();
        }
    }

    private function generateScript(){
        if($this->data['dbList']&&!is_string($this->data['dbList'])){
            $dbList = maybe_json_encode(call_user_func($this->data['dbList']));
        }else{
            $dbList = maybe_json_encode(array_is_empty($this->data['dbList'])?[]:$this->data['dbList']);
        }

        $html = '<script src="'.assets_url('/static/dist/jquery-ui-1.12.1.sortable-drable/jquery-ui.min.js').'" type="text/javascript"></script>
                <script type="text/javascript">
                    window.formBuilder = {
                        dbNameList:'.$dbList.',
                        editor:{},
                        handleOptionCustomSwitch:function(optionKey){
                           let isOpen =  $("#wpform-config-field-"+optionKey+"-custom:checked").length>0;
                           if(isOpen){
                                $("#wpform-config-field-"+optionKey+"-custom-value").removeAttr("readonly");
                           }else{
                                $("#wpform-config-field-"+optionKey+"-custom-value").attr("readonly","readonly").val("");
                           }
                        },
                        buildOptionLine:function(optionKey,index,key,value){
                            if(value){
                                value = $.trim(value.replace(/[\'"]/g,""));
                            }else{
                                value="";
                            }
                            return "<li style=\\"margin-bottom: 5px;\\" id=\\"wpform-config-field-"+optionKey+"-"+index+"\\"><div class=\\"d-flex flex-row align-items-center\\"><input class=\\"form-control option-key\\" data-index=\\""+index+"\\" style=\\"width:40px;\\" type=\\"text\\" value=\\""+(key?key:"")+"\\" placeholder=\\"选项值\\" />==><input class=\\"form-control option-value\\" data-index=\\""+index+"\\" style=\\"width:70px;margin-right:10px;\\" type=\\"text\\" value=\\""+value+"\\" placeholder=\\"选项内容\\" />  <a href=\\"javascript:void(0);\\" style=\\"width:20px;height:20px;border:solid 1px #d2d2d2;border-radius:50%!important;\\" class=\\"d-flex justify-content-center align-items-center\\" onclick=\\"window.formBuilder.optionToUp(\'"+optionKey+"\',"+index+");\\" title=\\"上移\\">↑</a> <a href=\\"javascript:void(0);\\"  style=\\"width:20px;height:20px;margin-left:5px;border:solid 1px #d2d2d2;border-radius:50%!important;\\" class=\\"d-flex justify-content-center align-items-center\\"  onclick=\\"window.formBuilder.optionToDown(\'"+optionKey+"\',"+index+");\\" title=\\"下移\\">↓</a><a href=\\"javascript:void(0);\\" style=\\"padding:0 10px;\\" style=\\"margin-left:5px;color:red;\\" onclick=\\"window.formBuilder.removeOption(\'"+optionKey+"\',"+index+");\\" title=\\"删除\\"><i class=\\"fa fa-trash\\"></i></a></li>";
                        },
                        addOptionLine:function(optionKey){
                            let index = (new Date()).getTime();
                            $("#wpform-config-field-"+optionKey).append(this.buildOptionLine(optionKey,index));
                        },
                        optionToUp:function(optionKey,index){
                             let values = {};
                             let lastIndex = null;
                             let theLastIndex = null;
                             
                             $("#wpform-config-field-"+optionKey+" li").each(function(){
                                 let $option = $(this).find("input.option");
                                 let theIndex = $option.data("index");
                                 
                                 if(theIndex==index){
                                    lastIndex = theLastIndex;
                                 }
                                 
                                 theLastIndex = theIndex;
                                 values[$option.data("index")] = $.trim($option.val());
                             });
                             
                             if(typeof values[index]==="undefined"||lastIndex===null){
                                return;
                             }
                             
                             var item = values[index];
                             var item1 = values[lastIndex];
                             values[lastIndex] = item;
                             values[index] = item1;
                             var html="";
                             var p = 0;
                             for(var i in values){
                                html+=this.buildOptionLine(optionKey,p++,i,values[i]);
                             }
                             $("#wpform-config-field-"+optionKey).html(html);
                        },
                        optionToDown:function(optionKey,index){
                             var values = {};
                             var nextIndex = null;
                             var theNextIndex = null;
                             
                             $("#wpform-config-field-"+optionKey+" li").each(function(){
                                 var $option = $(this).find("input.option");
                                 var theIndex = $option.data("index");
                                 
                                 if(theNextIndex!==null){
                                    nextIndex = theIndex;
                                    theNextIndex = null;
                                 }
                                 
                                 if(theIndex==index){
                                    theNextIndex = theIndex;
                                 }
                                 
                                 values[$option.data("index")] = $.trim($option.val());
                             });
                             
                             if(typeof values[index]==="undefined"||nextIndex===null){
                                return;
                             }
                             
                             var item = values[index];
                             var item1 = values[nextIndex];
                             values[nextIndex] = item;
                             values[index] = item1;
                             var html="";
                             var pp = 0;
                             for(var i in values){
                                html+=this.buildOptionLine(optionKey,pp++,i,values[i]);
                             }
                             $("#wpform-config-field-"+optionKey).html(html);
                        },
                        removeOption:function(optionKey,index){
                            htmlhelper.dialog.confirm({
                                confirm:function(){
                                    $("#wpform-config-field-"+optionKey+"-"+index).remove();
                                }
                            });
                        },
                        onOptionOtherSwitchChange:function(optionKey){
                            if($("#wpform-config-field-"+optionKey+"-enable:checked").length>0){
                                 $("#wpform-config-field-"+optionKey+"-config").removeClass("hide").addClass("show");
                            }else{
                                 $("#wpform-config-field-"+optionKey+"-config").removeClass("show").addClass("hide");
                            }
                        },
                        removeFile:function(uploaderId,id){
                            htmlhelper.dialog.confirm({
                                title:\'确定删除？\',
                                confirm:function(){
                                    $("#"+uploaderId+"-"+id).remove();
                                }
                            })
                        },
                        htmlspecialchars:function(str){
                            if(!str ||typeof str!==\'string\'){
                                return "";
                            }
                            str = str.replace(/&/g, \'&amp;\');
                            str = str.replace(/</g, \'&lt;\');
                            str = str.replace(/>/g, \'&gt;\');
                            str = str.replace(/"/g, \'&quot;\');
                            str = str.replace(/\'/g, \'&#039;\');
                            return str;
                        },
                        generateFileHtml :function(uploaderId,id,file,fileJson){
                            var json = fileJson?this.htmlspecialchars(JSON.stringify(fileJson)):"";
                            return \'<li id="\'+uploaderId+\'-\' + id + \'" data-file="\'+json+\'" class="d-flex flex-row align-items-center">\' +
                                        \'<h4 class="info"><a class="name" target="_blank" style="max-width:120px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;">\' + (file.name?file.name:"") + \' </a> <small class="process"></small></h4>\' +
                                        \'<a class="remove" href="javascript:void(0);" style="margin-left:10px;" onclick="window.formBuilder.removeFile(\\\'\' + uploaderId + \'\\\',\\\'\' + id + \'\\\')">删除</a>\'+
                                    \'</li>\';
                        },
                        uploader:function(uploaderId){
                           var uploader = WebUploader.create({
                                swf: \'/static/plugins/webuploader/uploader.swf\',
                                auto: true, // 选完文件后，是否自动上传
                                server: \''.url("module/web.Func/uploadFile").'\',
                                formData:{
                                    key:\'file\'
                                },
                                pick:{
                                    id:\'#\'+uploaderId+" div .uploader",
                                    multiple:false
                                },
                                fileVal:\'file\',
                                fileNumLimit:1,
                                duplicate:true,
                                prepareNextFile:true,
                                // 开起分片上传。
                                chunked: true
                            });
                            var $list = $("#"+uploaderId+" .uploader-list");
                           
                            uploader.on( \'fileQueued\', function( file ) {
                                var count = $list.find("li").length;
                                if(count>=1){
                                    uploader.cancelFile( file );
                                    return;
                                }
                                $list.append(window.formBuilder.generateFileHtml(uploaderId,file.id,file,null) );
                            });
                           uploader.on( \'uploadProgress\', function( file, percentage ) {
                                $( \'#uploader-\'+file.id ).find(\'small.process\').text(\'(\'+(percentage * 100).toFixed(0)+\'%)\');
                            });
                            uploader.on( \'uploadSuccess\', function( file,response ) {
                                if(response.errcode!=0){
                                    $( \'#\'+uploaderId+\'-\'+file.id ).find(\'small.process\').text(\'(上传出错，errcode:\'+response.errcode+\',errmsg:\'+response.errmsg+\')\');
                                    return;
                                }
        
                                $( \'#\'+uploaderId+\'-\'+file.id ).find(\'.remove\').show();
                                $( \'#\'+uploaderId+\'-\'+file.id ).data(\'file\',{
                                    url:response.url,
                                    url_local:response.url_local,
                                    key:response.key,
                                    name:response.name,
                                    title:response.title
                                });
                                uploader.reset();
                                $( \'#\'+uploaderId+\'-\'+file.id ).find(\'small.process\').remove();
                                $( \'#\'+uploaderId+\'-\'+file.id ).find(".name").attr(\'href\',response.url);
                            });
                            uploader.on( \'uploadError\', function( file,reason) {
                                $( \'#\'+uploaderId+\'-\'+file.id ).find(\'small.process\').css(\'color\',\'red\').text(\'(上传出错，errcode:\'+reason+\')\');
                            });
                        },
                        getFieldTemplate:function(optionKey,option,value){
                            option = $.extend({
                                        "title":"未命名",
                                        "type":"text",
                                        "description":"",
                                        "required":false,
                                        "placeholder":"",
                                        "style":"",
                                        "default":""
                                    },option);
                            if(null===value||""===value){
                                value=option.default;
                            }
                            switch(option.type){
                                default:
                                case "text":
                                    return \'<div class="sui-form-field">\' +
                                                \'<label class="sui-label">\'+option.title+\' \'+(option.required?"<span class=\\"required\\">*</span>":"")+\'</label>\' +
                                                \'<span class="help-block">\'+option.description+\'</span>\'+
                                                \'<input id="wpform-config-field-\'+optionKey+\'" placeholder="\'+option.placeholder+\'" \'+(option.readonly?\'readonly\':\'\')+\' class="form-control" type="text" value="\'+value+\'" />\' +
                                                \'<span id="wpform-config-field-\'+optionKey+\'-message" class="sui-error-message"></span>\' +
                                           \'</div>\';   
                                case "textarea":
                                    return \'<div class="sui-form-field">\' +
                                                \'<label class="sui-label">\'+option.title+\' \'+(option.required?"<span class=\\"required\\">*</span>":"")+\'</label>\' +
                                                \'<span class="help-block">\'+option.description+\'</span>\'+
                                                \'<textarea rows="3" id="wpform-config-field-\'+optionKey+\'" placeholder="\'+option.placeholder+\'" class="form-control">\'+value+\'</textarea>\' +
                                                \'<span id="wpform-config-field-\'+optionKey+\'-message" class="sui-error-message"></span>\' +
                                           \'</div>\'; 
                                case "file":
                                    if(value&&typeof value!=="object"){
                                        try{
                                            value = $.parseJSON(value);
                                        }catch(e){}
                                    }
                                    
                                    var hh = "";
                                    if(value){
                                        var time = (new Date()).getTime();
                                       for(var p in value){
                                        hh+=window.formBuilder.generateFileHtml("wpform-config-field-"+optionKey,time+"-"+p,value[p],value[p]);
                                        }
                                       
                                    }
                                    return \'<div class="sui-form-field">\' +
                                                \'<label class="sui-label">\'+option.title+\' \'+(option.required?"<span class=\\"required\\">*</span>":"")+\'</label>\' +
                                                \'<span class="help-block">\'+option.description+\'</span>\'+
                                                \'<div class="d-flex flex-column" id="wpform-config-field-\'+optionKey+\'" >\'+
                                                    \'<ul class="uploader-list" style="list-style: none;padding:0;">\'+hh+\'</ul>\'+
                                                   \' <div class="d-flex flex-row">\'+
                                                      \' <div class="uploader">选择文件</div>\'+
                                                    \'</div>\'+
                                                \'</div>\' +
                                                \'<span id="wpform-config-field-\'+optionKey+\'-message" class="sui-error-message"></span>\' +
                                                \'<script type="text/javascript">window.formBuilder.uploader("wpform-config-field-\'+optionKey+\'");<\/script>\'+
                                           \'</div>\'; 
                                    
                               case "editor":
                                   if(this.editor[optionKey]){
                                       this.editor[optionKey].destroy();
                                       $("#wpform-config-field-"+optionKey).remove();
                                   }
                                    return \'<div class="sui-form-field">\' +
                                                \'<label class="sui-label">\'+option.title+\' \'+(option.required?"<span class=\\"required\\">*</span>":"")+\'</label>\' +
                                                \'<script id="wpform-config-field-\'+optionKey+\'" type="text/plain">\'+value+\'<\/script>\' +
                                                \'<span id="wpform-config-field-\'+optionKey+\'-message" class="sui-error-message"></span>\' +
                                           \'</div><script type="text/javascript">window.formBuilder.editor.\'+optionKey+\' = UE.getEditor("wpform-config-field-\'+optionKey+\'",{scaleEnabled:true, allowDivTransToP:false,zIndex:9994 });<\/script>\';                                          
                                case "select":
                                    var list = option.options?option.options:[];
                                    var str = "";
                                    for(var listIndex in list){
                                        str+="<option "+(listIndex==value?"selected":"")+" value=\""+listIndex+"\">"+list[listIndex]+"</option>";
                                    }
                                     return \'<div class="sui-form-field">\' +
                                                \'<label class="sui-label">\'+option.title+\' \'+(option.required?"<span class=\\"required\\">*</span>":"")+\'</label>\' +
                                                \'<span class="help-block">\'+option.description+\'</span>\'+
                                               \' <select class="form-control"  id="wpform-config-field-\'+optionKey+\'">\'+str+\'</select>\' +
                                                \'<span id="wpform-config-field-\'+optionKey+\'-message" class="sui-error-message"></span>\' +
                                           \'</div>\'; 
                                case "checkbox":
                                    return \'<div class="sui-form-field">\'+
                                                \'<label class="sui-label">\' +
                                                     \'<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">\' +
                                                       \' <input type="checkbox" class="checkboxes" value="yes" id="wpform-config-field-\'+optionKey+\'" \'+(value?"checked":"")+\' />\' +
                                                       \' <span></span>\' +
                                                   \' </label>\' +
                                                     \'<span class="sui-toggle-label" style="margin-left:5px;">\'+option.title+\'</span>\' +
                                                 \'</label>\' +
                                                 \'<span class="help-block">\'+option.description+\'</span>\'+
                                                 \'<span id="wpform-config-field-\'+optionKey+\'-message" class="sui-error-message"></span>\' +
                                            \'</div>\';
                                case "option":
                                    if(!value){
                                        value =[];
                                    }         
                                    var html =  \'<div class="sui-form-field">\' +
                                                \'<label class="sui-label">\'+option.title+\' \'+(option.required?"<span class=\\"required\\">*</span>":"")+\'</label>\' +
                                                \'<span class="help-block">\'+option.description+\'</span>\'+
                                                  \'<ul id="wpform-config-field-\'+optionKey+\'" class="list-unstyled wpform-option">\';
                                               var pp = 0;
                                                  for(var index in value){
                                                      html+=this.buildOptionLine(optionKey,pp++,index,value[index]);
                                                  }
                                        
                                        html+=    \'</ul>\'+
                                                \'<ul class="list-unstyled"><li><button class="btn btn-success btn-xs" type="button" onclick="window.formBuilder.addOptionLine(\\\'\'+optionKey+\'\\\');">新增一项</button></li></ul>\' +
                                                \'<span id="wpform-config-field-\'+optionKey+\'-message" class="sui-error-message"></span>\' +
                                           \'</div>\';  
                                    return html;
                                 case "option_other":
                                    var html = \'<div class="sui-form-field">\'+
                                                \'<label class="sui-label">\' +
                                                     \'<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">\' +
                                                       \' <input type="checkbox" onchange="window.formBuilder.onOptionOtherSwitchChange(\\\'\'+optionKey+\'\\\');"  class="checkboxes" value="yes" id="wpform-config-field-\'+optionKey+\'-enable" \'+(value&&value.enable?"checked":"")+\' />\' +
                                                       \' <span></span>\' +
                                                   \' </label>\' +
                                                     \'<span class="sui-toggle-label" style="margin-left:5px;">开启“其他”选项</span>\' +
                                                 \'</label>\' +
                                            \'</div>\';
                                            
                                     html+=\'<div style="border:solid 1px #f2f2f2;padding:10px;margin-bottom:20px;" class="\'+(value&&value.enable?"show":"hide")+\'" id="wpform-config-field-\'+optionKey+\'-config">\' +
                                                 \' <div class="sui-form-field">\' +
                                                     \'<label class="sui-label">\' +
                                                        \'<label class="sui-label">“其他”选项标题</label>\' +
                                                        \'<input id="wpform-config-field-\'+optionKey+\'-title" placeholder="其他" class="form-control" type="text" value="\'+(value?value.title:"")+\'" />\' +
                                                     \' </label>\' +
                                                   \'</div>\'+
                                                    \' <div class="sui-form-field">\' +
                                                    \'<label class="sui-label">\' +
                                                        \'<label class="sui-label">“其他”选项Placeholder</label>\' +
                                                        \'<input id="wpform-config-field-\'+optionKey+\'-placeholder"  class="form-control" type="text" value="\'+(value?value.placeholder:"")+\'" />\' +
                                                     \' </label>\' +
                                                   \'</div>\'+
                                                   \' <div class="sui-form-field">\' +
                                                         \'<label class="sui-label">\' +
                                                                \'<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">\' +
                                                                   \' <input type="checkbox" class="checkboxes" value="yes" id="wpform-config-field-\'+optionKey+\'-required" \'+(value?"checked":"")+\' />\' +
                                                                   \' <span></span>\' +
                                                               \' </label>\' +
                                                                 \'<span class="sui-toggle-label" style="margin-left:5px;">“其他”选项内容必填</span>\' +
                                                            \' </label>\' +
                                                   \'</div>\'+
                                           \'</div>\' ;
                                       return html;
                                           
                            }
                        },
                        getFieldData:function(optionKey,option){
                             option = $.extend({
                                        "type":"text"
                                      },option);
                             switch(option.type){
                                 default:
                                 case "text":
                                 case "textarea":
                                 case "select":
                                     return $.trim($("#wpform-config-field-"+optionKey).val().replace(/[\'"]/g,""));
                                 case "editor":
                                     return this.editor[optionKey].getContent();
                                 case "file":
                                     var file = $("#wpform-config-field-"+optionKey+" .uploader-list li").first().data("file");
                                    if(file){
                                        return JSON.stringify([file]);
                                    }
                                    return null;
                                 case "checkbox":
                                     return $("#wpform-config-field-"+optionKey+":checked").length>0; 
                                 case "option":
                                     var values = {};
                                     $("#wpform-config-field-"+optionKey+" li").each(function(){
                                         var key =$.trim($(this).find("input.option-key").val());
                                          var val =$.trim($(this).find("input.option-value").val());
                                         if(!key||!val){
                                            return;
                                         }
                                         
                                         values[key?key:val] = val;
                                     });
                                     return values;
                                 case "option_other":
                                     return {
                                        enable:$("#wpform-config-field-"+optionKey+"-enable:checked").length>0,
                                        title:$("#wpform-config-field-"+optionKey+"-title").val(),
                                        placeholder:$("#wpform-config-field-"+optionKey+"-placeholder").val(),
                                        required:$("#wpform-config-field-"+optionKey+"-required:checked").length>0
                                     };
                             }
                        },
                        clearFieldError:function(){
                            $(".sui-error-message").empty();
                        },
                        setFieldError:function(optionKey){
                             $("#wpform-config-field-"+optionKey+"-message").html(optionKey);
                        }
                    };
                </script>';

        $control = new ControlFBuilder();
        foreach ($control->getComponents() as $groupKey=> $group){
            foreach ($group['options'] as $fieldKey=>$field){
                ob_start();
                $res = call_user_func("{$field['class']}::generateScript");
                if($res &&$res instanceof \think\Response){
                    echo $res->getContent();
                }
                $html.=ob_get_clean();
            }
        }

        return $html.'<script type="text/javascript">
                        jQuery(function($){
                            $(document).trigger("handle_form_editor_init");
                        });
                      </script>';
    }
}