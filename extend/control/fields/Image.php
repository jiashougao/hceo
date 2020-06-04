<?php
namespace control\fields;

use think\Exception;
use think\Response;

class Image extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'max'=>1,
        'class' => array(),
        'style' => array(),
        //图片切割尺寸
        'size'=>null,//array(
//            'width'=>200,
//            'height'=>200
        //),
        'default'=>null,
        'value'=>null,
        'custom_attributes' => array ()
    );

    public function getValue($format=false)
    {
        if(!$format){
            return parent::getValue();
        }
        return maybe_json_decode(parent::getValue());
    }

    public function preview($strip_tags = false,$values=null){
        $value = maybe_json_decode($this->getValue());
        if(array_is_empty($value)){$value=[];}

        if($strip_tags){
            $str = "";
            foreach ($value as $img){
                if($str){$str.=",";}
                $str.=$img&&is_array($img)&&isset($img['url'])?$img['url']:null;
            }
            return $str;
        }

        ob_start();
        ?>
        <ul style="list-style: none;padding:0;margin:0">
            <?php foreach ($value as $file){
                ?><li><a href="<?php echo esc_attr($file&&is_array($file)&&isset($file['url'])?$file['url']:null)?>" target="_blank" class="d-flex align-items-center justify-content-center" style="border:solid 1px #f2f2f2;width:80px;height:80px;"><img src="<?php echo esc_attr($file&&is_array($file)&&isset($file['url'])?$file['url']:null)?>" style="max-width: 80px;max-height: 120px;" /></a></li><?php
                break;
            }?>
        </ul>
        <?php
        return ob_get_clean();
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );
        $value =maybe_json_decode($this->getValue());
        ?>
        <style type="text/css">
            .webuploader-container {
                position: relative;
            }
            .webuploader-element-invisible {
                width: 100px;
                height: 50px;
                position: absolute !important;
                clip: rect(1px 1px 1px 1px); /* IE6, IE7 */
                clip: rect(1px,1px,1px,1px);
            }
            .webuploader-pick {
                position: relative;
                display: inline-block;
                cursor: pointer;
                background: #00b7ee;
                padding: 10px 15px;
                color: #fff;
                text-align: center;
                border-radius: 3px;
                overflow: hidden;
            }
            .webuploader-pick-hover {
                background: #00a2d4;
            }

            .webuploader-pick-disable {
                opacity: 0.6;
                pointer-events:none;
            }

            .webuploader-container>div{width: 86px;height: 39px;}
        </style>
        <div class="d-flex flex-column">
            <!--用来存放文件信息-->
            <ul id="<?php echo $field?>-file-list" class="uploader-list d-flex flex-row" style="list-style: none;padding:0;">

            </ul>
            <div class="d-flex flex-row">
                <div id="<?php echo $field?>-file"><?php echo __('选择图片')?></div>
            </div>
        </div>
        <script type="text/javascript">
            (function($,undefined){
                $(function(){
                    window.<?php echo $field?>View={
                        remove:function(id){
                            if(window.confirm('<?php echo __("确定删除？")?>')){
                                $("#<?php echo $field?>-"+id).remove();
                                window.<?php echo $field?>View.uploader.reset();
                                $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                                    column:"<?php echo $field?>",
                                    event:'keyup',
                                    value:JSON.stringify(window.<?php echo $field?>View.getValue())
                                });
                            }
                        },
                        html:function(id,file){
                            return '<li id="<?php echo $field?>-' + id + '" class="d-flex flex-column align-items-center" style="margin-right:10px;">' +
                                        '<a target="_blank" class="img-container d-flex align-items-center justify-content-center" style="border:solid 1px #f2f2f2;width:120px;height:120px;"><img src=""  style="max-width:100%;max-height:100%;" class="img"/></a>' +
                                        '<h4 class="info d-flex flex-row"><span style="max-width:120px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;">' + (file.name?file.name:"") + ' </span> <small class="process"></small></h4>' +
                                        '<a class="remove" href="javascript:void(0);" style="margin-left:10px;" onclick="<?php echo $field?>View.remove(\'' + id + '\')"><?php echo __("删除")?></a>'+
                                    '</li>';
                        },
                        getValue:function(){
                            var files = [];
                            $("#<?php echo $field?>-file-list li").each(function(){
                                var res = $(this).data('file');
                                if(res){
                                    files.push(res);
                                }
                            });
                            return files;
                        }
                    };
                    var $list = $("#<?php echo $field?>-file-list");
                    var config = {
                        swf: '/static/plugins/webuploader/uploader.swf',
                        auto: <?php echo $this->data['disabled']?'false':'true'?>, // 选完文件后，是否自动上传
                        server: '<?php echo url("module/web.Func/uploadFile")?>',
                        formData:{
                            key:'file'
                        },
                        accept: {
                            title: '图片'//,
                           // extensions: 'jpg,jpeg,png,gif,bmp',
                           // mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif,image/bmp'
                        },
                        pick:{
                            id:'#<?php echo $field?>-file',
                            multiple:<?php echo absint($this->data['max'])>1?'true':'false'?>
                        },
                        fileVal:'file',
                       // fileNumLimit:<?php echo absint($this->data['max'])?>,
                        duplicate:true,
                        prepareNextFile:true,
                        // 开起分片上传。
                        chunked: true
                    };
                    <?php if(!array_is_empty($this->data['size'])){ ?>
                        config.thumb={
                            width: <?php echo isset($this->data['size']['width'])?absint($this->data['size']['width']):450?>,
                            height: <?php echo isset($this->data['size']['height'])?absint($this->data['size']['height']):450?>,
                            // 图片质量，只有type为`image/jpeg`的时候才有效。
                            quality: 100,
                            // 是否允许放大，如果想要生成小图的时候不失真，此选项应该设置为false.
                            allowMagnify: false,
                            // 是否允许裁剪。
                            crop: true
                        };
                <?php }?>
                    window.<?php echo $field?>View.uploader = WebUploader.create(config);

                    window.<?php echo $field?>View.uploader.on( 'fileQueued', function( file ) {
                        let count = $("#<?php echo $field?>-file-list li").length;
                        if(count>=<?php echo absint($this->data['max'])?>){
                            //window.<?php //echo $field?>//View.uploader.cancelFile( file );
                            //return;
                            $list.find('>li:first').remove();
                        }

                        $list.append(window.<?php echo $field?>View.html(file.id,file) );
                    });
                    window.<?php echo $field?>View.uploader.on( 'uploadProgress', function( file, percentage ) {
                        $( '#<?php echo $field?>-'+file.id ).find('small.process').text('('+(percentage * 100).toFixed(0)+'%)');
                    });
                    window.<?php echo $field?>View.uploader.on( 'uploadSuccess', function( file,response ) {
                        if(response.errcode!=0){
                            $( '#<?php echo $field?>-'+file.id ).find('small.process').text('(上传出错，errcode:'+response.errcode+',errmsg:'+response.errmsg+')');
                            return;
                        }

                        $( '#<?php echo $field?>-'+file.id ).find('.remove').show();
                        $( '#<?php echo $field?>-'+file.id ).data('file',{
                            url:response.url,
                            url_local:response.url_local,
                            key:response.key,
                            name:response.name,
                            title:response.title,
                            width:response.width,
                            height:response.height
                        });
                        $( '#<?php echo $field?>-'+file.id ).find('small.process').remove();
                        $( '#<?php echo $field?>-'+file.id ).find(".img-container")
                            .attr('href',response.url);
                        $( '#<?php echo $field?>-'+file.id ).find(".img")
                            .attr('src',response.url);

                        $(document).trigger("handle_<?php echo $form_id?>_column_change",{
                            column:"<?php echo $field?>",
                            event:'keyup',
                            value:JSON.stringify(window.<?php echo $field?>View.getValue())
                        });
                    });
                    window.<?php echo $field?>View.uploader.on( 'uploadError', function( file,reason) {
                        $( '#<?php echo $field?>-'+file.id ).find('small.process').css('color','red').text('(上传出错，errcode:'+reason+')');
                    });

                    $(document).bind("handle_<?php echo $form_id?>_submit",function(e,form){
                        var files = window.<?php echo $field?>View.getValue();
                        form.<?php echo esc_attr($this->key)?> = JSON.stringify(files);
                    });

                    window.set_field_<?php echo $field?>_value = function(value){
                        var $container = $("#<?php echo $field?>-file-list").empty();
                        if(!value){
                            return;
                        }

                        for(var index in value){
                            var res  = value[index];
                            var id = "handle-"+index;
                            $container.append(window.<?php echo $field?>View.html(id,res));
                            $( '#<?php echo $field?>-'+id ).data('file',res);

                            $( '#<?php echo $field?>-'+id ).find(".img-container")
                                .attr('href',res.url);
                            $( '#<?php echo $field?>-'+id ).find(".img")
                                .attr('src',res.url);
                        }
                    };
                    window.set_field_<?php echo $field?>_value(<?php echo array_is_empty($value)?"[]":maybe_json_encode($value);?>);
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * @param $res
     * @param null $call
     * @return mixed
     * @throws Exception
     */
    public function param(&$res,$call=null){
        $value = parent::param($res,$call);
        if(isset($this->data['required'])&&$this->data['required']){
            $img = maybe_json_decode($value,true);
            if(array_is_empty($img)){
                throw new Exception(errorMessage(10000,array($this->data['title'])),10000);
            }
        }

        return $value;
    }
}