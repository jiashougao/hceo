<?php
namespace control\fields;

class File extends Base
{
    public $default = array (
        'required'=>false,
        'disabled' => false,
        'max'=>1,
        'class' => array(),
        'style' => array(),
        'default'=>null,
        'value'=>'',
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
                $str.=$img['url'];
            }
            return $str;
        }

        ob_start();
        ?>
        <ul style="list-style: none;padding:0;margin:0">
            <?php foreach ($value as $file){
                ?><li><a href="<?php echo esc_attr($file['url'])?>" target="_blank" style="max-width:120px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;"><?php echo $file['name']?></a></li><?php
                break;
            }?>
        </ul>
        <?php
        return ob_get_clean();
    }

    public function generateField($form_id) {
        $field = $this->getFieldKey ( $form_id );
        $value = maybe_json_decode($this->getValue());
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
            <ul id="<?php echo $field?>-file-list" class="uploader-list" style="list-style: none;padding:0;">

            </ul>
            <div class="d-flex flex-row">
                <div id="<?php echo $field?>-file">选择文件</div>
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
                            return '<li id="<?php echo $field?>-' + id + '" class="d-flex flex-row align-items-center">' +
                                        '<h4 class="info"><a class="name" target="_blank" style="max-width:120px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;">' + (file.name?file.name:"") + ' </a> <small class="process"></small></h4>' +
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
                    window.<?php echo $field?>View.uploader = WebUploader.create({
                        swf: '/static/plugins/webuploader/uploader.swf',
                        auto: <?php echo $this->data['disabled']?'false':'true'?>, // 选完文件后，是否自动上传
                        server: '<?php echo url("module/web.Func/uploadFile")?>',
                        formData:{
                            key:'file'
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
                    });

                    window.<?php echo $field?>View.uploader.on( 'fileQueued', function( file ) {
                        var count = $("#<?php echo $field?>-file-list li").length;
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
                            title:response.title
                        });
                        $( '#<?php echo $field?>-'+file.id ).find('small.process').remove();
                        $( '#<?php echo $field?>-'+file.id ).find(".name").attr('href',response.url);

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
                            $( '#<?php echo $field?>-'+id ).find(".name").attr('href',res.url);
                        }
                    };
                    window.set_field_<?php echo $field?>_value(<?php echo array_is_empty($value)?"[]":maybe_json_encode($value);?>);
                });
            })(jQuery);
        </script>
        <?php
    }

}