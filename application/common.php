<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Response;
use think\facade\Lang;
// 应用公共文件


/**
 * 返回格式化的图片URL
 * 数据库中存储的图片地址为JSON，方便获取url
 *
 * @since v1.0.0
 * @author ranj
 * @param array|string $imgJson 图片类型数据
 * @param string $default
 * @return string
 */
function esc_img_url($imgJson,$default = "https://server.messecloud.com/static/dist/img/blank.jpg"){
    if(is_string($imgJson)&&(strpos($imgJson,'http')===0||strpos($imgJson,'/')===0)){
        return $imgJson;
    }

    $imgList = maybe_json_decode($imgJson,true);
    $first =  array_is($imgList)&&count($imgList)?$imgList[0]:null;
    return esc_url($first&&array_is($first)&&isset($first['url'])?$first['url']:$default);
}


/**
 * url 数据格式化，清除异常字符等
 *
 * @since v1.0.0
 * @author ranj
 * @param string $url 链接地址
 * @return string
 */
function esc_url( $url) {
    if ( '' === $url ||is_null($url))
        return $url;

    $url = str_replace( ' ', '%20', $url );
    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url);

    if ( '' === $url ) {
        return $url;
    }

    if ( 0 !== stripos( $url, 'mailto:' ) ) {
        $strip = array('%0d', '%0a', '%0D', '%0A');
        $call = function ( $search, $subject ) {
            $subject = (string) $subject;

            $count = 1;
            while ( $count ) {
                $subject = str_replace( $search, '', $subject, $count );
            }

            return $subject;
        };
        $url = $call($strip, $url);
    }

    $url = str_replace(';//', '://', $url);
    /* If the URL doesn't appear to contain a scheme, we
     * presume it needs http:// prepended (unless a relative
     * link starting with /, # or ? or a php file).
     */
    if ( strpos($url, ':') === false && ! in_array( $url[0], array( '/', '#', '?' ) ) &&! preg_match('/^[a-z0-9-]+?\.php/i', $url) )
        $url = 'https://' . $url;

    return preg_replace("/\&amp;/","&",$url);
}


/**
 * 获取域名
 *
 * @since v1.0.0
 * @author ranj
 * @param bool $strict  true 仅仅获取HOST(排除端口信息)
 * @return string
 */
function get_host($strict=true){
    return request()->host($strict);
}

/**
 * 获取顶级域名
 *
 * @since v1.0.0
 * @author ranj
 * @return string
 */
function get_root_domain(){
    return request()->rootDomain();
}

/**
 * 获取当前二级域名标识
 *
 * @since v1.0.0
 * @author ranj
 * @param string $host 域名
 * @return string
 */
function get_sub_domain($host=null){
    if(!$host){
        return request()->subDomain();
    }

    $host = explode('.', $host, -2);
    return  implode('.', $host);
}

/**
 * 设置域名
 *
 * @since v1.0.0
 * @author ranj
 * @param string $sub 二级域名
 * @return string
 */
function set_host($sub){
    return $sub .".".get_root_domain();
}

/**
 * 设置域名
 *
 * @since v1.0.0
 * @author ranj
 * @param string $sub 二级域名
 * @param null $scheme
 * @return string
 */
function set_domain($sub,$scheme=null){
    return ($scheme?$scheme:(request()->scheme() . '://')). $sub .".".get_root_domain();
}

/**
 * 判断是否为array 数据
 *
 * @since v1.0.0
 * @author ranj
 * @param array $array
 * @return bool
 */
function array_is($array=array()){
    if(!$array||!is_array($array)){
        return false;
    }
    return true;
}

/**
 * Json Decode 安全的解析
 *
 * @since v1.0.0
 * @author ranj
 * @param string|array $data
 * @param bool $assoc
 * @return array|object
 */
function maybe_json_decode($data,$assoc=true){
    if(!$data){
        return $assoc?array():new stdClass();
    }

    if(is_array($data)||is_object($data)){
        return $data;
    }

    if(is_string($data)){
        $result =  json_decode($data,$assoc);
        if(!$result){
            return $assoc?array():new stdClass();
        }
        return $result;
    }

    return array();
}

/**
 * json_encode
 *
 * @param $data
 * @return false|string
 */
function maybe_json_encode($data){
    if(array_is($data)){
        return json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }

    if(!$data){
        return json_encode(array(),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);;
    }

    if(is_string($data)){
        return $data;
    }

    return json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
}

/**
 * Notes: http get 请求
 * @param $url
 * @param bool $cache 标识是否缓存请求数据
 * @return array|object
 * @throws \think\Exception
 */
function http_get($url,$cache = true){
    $cacheKey = "page:".md5($url);
    if($cache){
        $data =  \think\facade\Cache::get($cacheKey);
        if($data){
            return $data;
        }
    }

    $url= config('app.api_host').'/'.$url;
    $response= maybe_json_decode(\org\helper\HttpHelper::GET($url));
    $data = isset($response['data']) ? $response['data']:null;
    if($cache){
        \think\facade\Cache::set($cacheKey,$data,config('cache.page'));
    }
    return $data;
}

/**
 * Notes: http post 请求
 * @param $url
 * @param array $request
 * @return array|object
 * @throws \think\Exception
 */
function http_post($url,$request=[]){
    $url= config('app.api_host').'/'.$url;
    return maybe_json_decode(\org\helper\HttpHelper::POST($url,$request));
}

/**
 * 加载模板输出
 *
 * @since v1.0.0
 * @author ranj
 * @param  string $template 模板文件名
 * @param  array  $vars     模板输出变量
 * @param  array  $config   模板参数
 * @return Response
 */
function fetch($template = '', $vars = [], $config = []){
    return Response::create($template, 'view')->assign($vars)->config($config);
}


function pc_page($pageIndex=1,$total_count=0,$pageSize=8,$url=""){
    $paging = paging([
        'page_index'=>$pageIndex,
        'total_count'=>$total_count,
        'page_size'=>$pageSize
    ]);
    $page_html ="";
    $page_html.=  fetch('common@page/pc_page',[
        'paging'=>$paging,
        'url' => config('app.api_host').'/'.$url,
    ])->getContent();
    return $page_html;
}

function mobile_page($url){
    $page_html ="";
    $page_html.=  fetch('common@page/mobile_page',[
        'url' => config('app.api_host').'/'.$url,
    ])->getContent();
    return $page_html;
}

/**
 * 分页数据处理
 * @param $args
 * @return array
 */
function paging($args){
    $args = parse_args($args,array(
        'page_index'=>1,
        'total_count'=>0,
        'page_size'=>20
    ));
    $pageIndex = $args['page_index'];
    $total_count = $args['total_count'];
    $pageSize = $args['page_size'];
    $url_count=5;

    $page_count = ceil($total_count/($pageSize*1.0));
    return array(
        'page_index'=> $pageIndex,
        'start_page_index'=>($pageIndex - $url_count) > 0 ? ($pageIndex - $url_count) : 1,
        'end_page_index'=>($pageIndex + $url_count) <= $page_count ? ($pageIndex + $url_count) : $page_count,
        'from_index'=>$page_count==0?0:(($pageIndex - 1) * $pageSize + 1),
        'to_index'=>($pageIndex >= $page_count||$page_count==0) ? $total_count : ($pageIndex * $pageSize),
        'is_last_page'=> $pageIndex >= $page_count || $page_count == 0,
        'is_first_page'=>$pageIndex == 1 || $page_count == 0,
        'page_count'=>$page_count,
        'page_size'=>$pageSize,
        'total_count'=>$total_count,
        'url_count'=>$url_count,
    );
}

/**
 * 声明默认数组参数，没有则自动补齐
 *
 * args key值在defaults 范围外，保持
 * args 相对defaults 缺少的key值 ，自动补齐，默认值从defaults获取
 * 例子： $defaults =[a=>1,c=>2]
 *     [a=>33,c=>2,b=>44] =>  [a=>33,c=>2,b=>44]
 *     [a=>22] =>[a=>22,c=>2]
 *
 * @since v1.0.0
 * @author ranj
 * @param array|object|string $args 待过滤的数组
 * @param array $defaults 参数范围 [key=>默认值,key1=>默认值1]
 * @return array
 */
function parse_args( $args, $defaults = array() ) {
    if(!$defaults||!is_array($defaults)){
        $defaults = array();
    }

    if(!$args||!is_array($args)){
        return $defaults;
    }
    if ( is_object( $args ) ){
        $r = get_object_vars( $args );
    }

    elseif ( is_array( $args ) ){
        $r =& $args;
    }
    return array_merge( $defaults, $r );
}

/**
 * 获取当前语言
 * zh-cn en-us
 * @return string
 */
function get_lang(){
    return Lang::range();
}

/**
 * 语言翻译
 *
 * '文件格式: {:format},文件大小：{:size}'   => [format=>xxx,size:xxx]
 *
 * @param string $text 待翻译的文字
 * @param array $args 模板语言中占位参数
 * @return string
 */
function __($text,$args=[]){
    return Lang::get($text,$args);
}

/**
 * 检测是否使用手机访问
 * @access public
 * @return bool
 */
function is_terminal_mobile()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $is_pc = (strpos($agent, 'windows nt')) ? true : false;
    $is_mac = (strpos($agent, 'mac os')) ? true : false;
    $is_iphone = (strpos($agent, 'iphone')) ? true : false;
    $is_android = (strpos($agent, 'android')) ? true : false;
    $is_ipad = (strpos($agent, 'ipad')) ? true : false;


    if($is_pc){
        return  'pc';
    }

    if($is_mac){
        return  'mobile';
    }

    if($is_iphone){
        return  'mobile';
    }

    if($is_android){
        return  'mobile';
    }

    if($is_ipad){
        return  'mobile';
    }
}

