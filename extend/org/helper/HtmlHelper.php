<?php
namespace org\helper;

/**
 * 菜单高亮，菜单url转换等
 * @since v1.0.0
 * @author ranj
 * Class HtmlHelper
 * @package org\helper
 */
class HtmlHelper{
    private static $_action,$_controller,$_module;
    private static $_params;

    /**
     * 获取当前高亮菜单的链接
     * @param array $menuUrl
     * @return string
     */
    public static function isCurrentMenu(array $menuUrl){
        $menuParams=null;
        if(count($menuUrl)>=4){
            $menuParams = $menuUrl[3];
            if(array_is_empty($menuParams)){
                $menuParams=null;
            }
        }
        $currentUrl = [self::get_current_module(),self::get_current_controller(),self::get_current_action()];

        //判断URL主题是否一致
        $newUrl = [];
        for($index = 0;$index<count($menuUrl)&&$index<3;$index++){
            $newUrl[]=$menuUrl[$index];
        }

        if(UrlHelper::urlArrToStr($newUrl)!=UrlHelper::urlArrToStr($currentUrl)){
            return false;
        }

        //判断参数是否有包含
        $params = self::get_current_params();
        if($params&&count($params)){
            if(!$menuParams){
                return false;
            }
            foreach ($params as $k=>$v){
                if(!isset($menuParams[$k])||$menuParams[$k]!=$v){
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 获取当前高亮菜单的参数
     * @return null
     */
    public static function get_current_params(){
        return  self::$_params&&!array_is_empty(self::$_params)?self::$_params:null;
    }

    /**
     * 获取当前高亮菜单的 action
     * @return string
     */
    public static function get_current_action(){
        return  self::$_action?self::$_action:request()->action();
    }

    /**
     * 获取当前高亮菜单的 controller
     * @return string
     */
    public static function get_current_controller(){
        return  self::$_controller?self::$_controller:request()->controller();
    }

    /**
     * 获取当前高亮菜单的 module
     * @return string
     */
    public static function get_current_module(){
        return  self::$_module?self::$_module:request()->module();
    }
    
    /**
     * 设置菜单高亮
     * @param string $action 选中的菜单的action
     * @param string $controller 选中的菜单的controller
     * @param string $module 选中的菜单的module
     */
    public static function setCurrentMenu($action='',$controller='',$module='',$params=array()){
        self::$_action= $action?$action:request()->action();
        self::$_controller = $controller?$controller:request()->controller();
        self::$_module = $module?$module:request()->module();
        self::$_params =$params;
    }

    public static function paging($args){
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

}