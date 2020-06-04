<?php
namespace org\helper;

/**
 * URL 操作
 *
 * Class UrlHelper
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class UrlHelper{
    /**
     * 生成二维码链接地址
     * @param $data
     * @return string
     */
    public static function qrcode($data){
        return url('module/web.func/qrcode',array(
            'data'=>base64_encode($data)
        ));
    }

    /**
     * 判断当前二级域名
     * @param $sub
     * @return bool
     */
    public static function isSubDomain($sub){
        return get_sub_domain()===$sub;
    }

    /**
     * 判断是否是手机浏览器端
     * @return bool
     */
    public static function isMinWebClient(){
        return request()->isMobile()
                ||in_array(HtmlHelper::get_current_module(),array('wxweb','miniweb'));
    }

    /**
     * 判断是微信内置浏览器或QQ浏览器
     * @return bool
     */
    public static function isWechatOrQQBrowserClient(){
        $ua = request()->header("user_agent");
        return $ua&&strripos($ua,'micromessenger')!==false;
    }

    /**
     * 判断是否是IOS客户端
     * @return bool
     */
    public static function isIOSClient(){
        $ua = request()->header("user_agent");
        return strripos($ua,'iphone')!==false||strripos($ua,'ipad')!==false;
    }

    /**
     * 判断是否是android客户端
     * @return bool
     */
    public static function isAndroidClient(){
        $ua = request()->header("user_agent");
        return $ua&&strripos( $ua,'android')!==false;
    }

    /**
     * 获取当前url
     * @param array $params
     * @param null $sub string 指定二级域名
     * @return string
     */
    public static function get_location_uri($params = array(),$sub=null){
        if($sub){
            $url =  request()->scheme() . '://' .$sub.'.'.get_root_domain().request()->url(false);
        }else{
            $url =  request()->url(true);
        }

        if(!is_array($params)||!count($params)){
            return $url;
        }

        $query =  parse_url($url,PHP_URL_QUERY);
        $fragment =  parse_url($url,PHP_URL_FRAGMENT);

        $args=array();
        parse_str($query,$args);

        $p = strpos($url,'?');
        if($p!==false){
            $url = substr($url,0,$p);
        }
        $p = strpos($url,'#');
        if($p!==false){
            $url = substr($url,0,$p);
        }
        $args = parse_args($params,$args);
        if(count($args)){
            $url.="?".http_build_query($args);
        }

        if($fragment){
            $url.="#".$fragment;
        }
        return $url;
    }

    /**
     * 创建url
     * @param $url
     * @param array $params
     * @param null $sub
     * @return bool|string
     */
    public static function rebuildUrl($url,$params = array(),$sub=null){
        if(empty($url)){
            return $url;
        }
        if(!is_array($params)||!count($params)){
            return $url;
        }

        $query =  parse_url($url,PHP_URL_QUERY);
        $fragment =  parse_url($url,PHP_URL_FRAGMENT);

        $args=array();
        parse_str($query,$args);

        $p = strpos($url,'?');
        if($p!==false){
            $url = substr($url,0,$p);
        }
        $p = strpos($url,'#');
        if($p!==false){
            $url = substr($url,0,$p);
        }
        $args = parse_args($params,$args);
        if(count($args)){
            $url.="?".http_build_query($args);
        }

        if($fragment){
            $url.="#".$fragment;
        }
        return $url;
    }

    /**
     * url 数组转换成字符串
     * @param $array
     * @param string $default
     * @return string
     */
    public static function urlArrToStr($array,$default="#"){
        if(array_is_empty($array)||count($array)<3){
            return $default;
        }

        if(count($array)===3){
            return url(join("/",$array));
        }

        $new_array = array($array[0],$array[1],$array[2]);

        if(count($array)>4){
            return request()->scheme() . '://'.$array[4].url(join("/",$new_array),$array[3]);
        }

        return url(join("/",$new_array),$array[3]);
    }
}