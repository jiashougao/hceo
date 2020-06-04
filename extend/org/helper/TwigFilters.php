<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/3/13
 * Time: 08:53
 */

namespace org\helper;


use Twig_SimpleFilter;

/**
 * Twig 模板语言编译器
 *
 * Class TwigFilters
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class TwigFilters
{
    public static function getFilters(){
        $filters=[];
        $filters[] = new Twig_SimpleFilter('truncate',function($string,$characters = 100, $ending = '...'){
            return self::truncate($string,$characters,$ending);
        });
        $filters[] = new Twig_SimpleFilter('esc_attr',function($string){
            return esc_attr($string);
        });
        $filters[] = new Twig_SimpleFilter('esc_img_url',function($string,$default = "https://server.messecloud.com/static/dist/img/blank.jpg"){
            return esc_img_url($string,$default);
        });
        $filters[] = new Twig_SimpleFilter('json_encode',function($string){
            return maybe_json_encode($string);
        });
        $filters[] = new Twig_SimpleFilter('json_decode',function($string){
            return maybe_json_decode($string);
        });

        $filters[] = new Twig_SimpleFilter('ajax_url',function($action){
            return url("site/web.Home/ajax",['action'=>$action]);
        });

        $filters[] = new Twig_SimpleFilter('apply_filters',function($filter,...$args){
            $params = [$filter,null];
            foreach ($args as $arg){
                $params[]=$arg;
            }
            return call_user_func_array('apply_filters',$params);
        });

        $filters[] = new Twig_SimpleFilter('do_action',function($filter,...$args){
            $params = [$filter];
            foreach ($args as $arg){
                $params[]=$arg;
            }
            call_user_func_array('do_action',$params);
        });

        $filters[] = new Twig_SimpleFilter('form',function($form_id,$btnSubmitId){
            $form_id =absint($form_id);
            $site = request()->current_site;
            $form = db_default('site_form')
                ->where('id',$form_id)
                ->where('state','active')
                ->where('site_id',$site['id'])
                ->find();
            if(!$form){
                return "";
            }

            $fields = maybe_json_decode($form['columns']);
            fields_sort($fields);

            ob_start();
            foreach($fields as $optionKey=>$option){
                $control = new \control\ControlFBuilder();
                $control->generateField("form{$form_id}",$optionKey,$option);
            }

            ?>
            <script type="text/javascript">
				$(function(){
					$("<?php echo esc_attr($btnSubmitId)?>").off('click.form-submit').on('click.form-submit',function () {
						var res = {};
						res.__form_id__ = "<?php echo $form_id;?>";
						$(document).trigger("handle_form<?php echo $form_id;?>_submit",res);
                        $.ajax({
                            type: "POST",
                            url: "<?php echo url("site/web.form/submit")?>",
                            data: res,
                            timeout:8000,
                            dataType: "json",
                            success: function(e){
                                if(e.errcode!==0){
                                    bootbox.alert(e.errmsg);
                                    return;
                                }
                                let call ={
                                    e:e,
                                    callback:function(){
                                        bootbox.alert('提交成功');
                                        setTimeout(function(){
                                            location.reload();
                                        },1500);
                                    }
                                };
                                $(document).trigger("handle_form<?php echo $form_id;?>_submit_succeed",call);
                                call.callback();
                            },
                        });
					});

				})
			</script>
            <?php
            return ob_get_clean();
        });

        return $filters;
    }
    /**
     * 重载插件默认truncate 没有处理utf-8的字符
     *
     * @param $input
     * @param int $characters
     * @param string $ending
     * @return string
     */
    public static function truncate($input, $characters = 100, $ending = '...')
    {
        if (is_string($input) || is_numeric($input)) {
            if (mb_strlen($input,'utf-8') > $characters) {
                return mb_strimwidth($input, 0, $characters,$ending,'utf-8') ;
            }
        }

        return $input;
    }

}