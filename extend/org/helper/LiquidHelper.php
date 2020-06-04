<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/3/2
 * Time: 18:08
 */

namespace org\helper;
use Liquid\Template;
use Twig_Environment;
use Twig_Loader_Array;

/**
 * Liquid模板语言 解析器
 *
 * Class LiquidHelper
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class LiquidHelper
{
    private $template;
    public function __construct($html)
    {
        $this->template = new Template();
        $this->template->parse($html);
    }

    public function render(array $assigns = array(), $filters = array(), array $registers = array()){
        $filters[]=LiquidFilters::class;
        return $this->template->render($assigns,$filters,$registers);
    }


    public static function parse($template,array $assigns = array(), $filters = array()){
        if(!$template){
            return "";
        }

        return preg_replace_callback('/(?s)<script[^>]*((type="text\/template"[^>]*class="template")|(class="template"[^>]*type="text\/template"))[^>]*>(.*?)<\/script>/',function($m)use($assigns,$filters){
            if(count($m)!=5){
                return "";
            }
            $helper = new TwigHelper();
            return $helper->parse($m[4], $assigns,$filters);
        },$template);
    }
}


class LiquidFilters{
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

    public static function esc_attr($input)
    {
        if (is_string($input)) {
            return esc_attr($input);
        }

        return $input;
    }

    public static function esc_img_url($input,$default="https://server.messecloud.com/static/dist/img/blank.jpg"){
        return esc_img_url($input,$default);
    }
}
