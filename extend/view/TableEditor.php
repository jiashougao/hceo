<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/3/2
 * Time: 11:16
 */

namespace view;


use Exception;
use Liquid\Template;
use org\helper\LiquidHelper;
use org\helper\TwigHelper;
use org\pcdesign\SectionType;
use org\pcdesign\Terminal;
use think\Db;
use think\facade\Log;
use think\facade\Session;
use think\Response;
use think\route\dispatch\Callback;
use Twig_Environment;
use Twig_Extension_Sandbox;
use Twig_Loader_Array;
use Twig_Sandbox_SecurityPolicy;

/**
 * 网页设计器
 *
 * Class TableEditor
 * @since v1.0.0
 * @author ranj
 * @package view
 */
class TableEditor extends Table
{
    /**
     * 编辑器退出，跳转URL
     * @var string
     */
    protected $back_url;

    /**
     * 全局页面配置信息加载
     *
     * @var callable
     */
    protected $callLoadConfig;

    /**
     * 当前页面信息加载
     * @var callable
     */
    protected $callLoadPage;

    /**
     * 执行页面更新操作
     * @var callable
     */
    protected $callUpdate;

    /**
     * 执行页面查询操作
     * @var callable
     */
    protected $handleSearchPage;

    /**
     * 标识当前客户端类型
     * pc:桌面端
     * phone:手机
     *
     * @var string
     */
    protected $terminal;

    /**
     * 网页设计器变量容器
     *
     * @var array
     */
    protected $callRegisterAssign;

    /**
     * 网页设计器filter容器
     *
     * @var array
     */
    protected $callRegisterFilter;

    /**
     * @deprecated 暂时弃用
     * @var array
     */
    public static $pageMaps=[];

    /**
     * 判断当前正在编辑的页面是否是首页
     * @var callable
     */
    private $callIsHome;

    /**
     * 回档上一次页面编辑的内容
     *
     * @var callable
     */
    private $_goBackHistory;

    /**
     * @deprecated 暂时废弃
     * @param $class
     */
    public static function register($class){
       self::$pageMaps[]=$class;
    }

    public function __construct($args)
    {
        $args = parse_args($args, array(
            'back_url' => null,
            'terminal' => Terminal::PC
        ));

        $this->viewKey = "model";
        $this->terminal = $args['terminal'];
        $this->back_url = $args['back_url'];

        /**
         * 声明在编辑器环境下
         */
        define('IS_EDITOR_INNER',true);
    }

    /**
     * 注册变量
     *
     * @param callable $call
     */
    public function handleRegisterAssign($call){
        $this->callRegisterAssign = $call;
    }

    /**
     * 注册filter
     * @param callable $call
     */
    public function handleRegisterFilter($call){
        $this->callRegisterFilter = $call;
    }

    /**
     * 注册更新操作
     * @param callable $call
     */
    public function handleUpdate($call)
    {
        $this->callUpdate = $call;
    }

    /**
     * 注册页面加载
     * @param callable $call
     */
    public function handleLoadPage($call)
    {
        $this->callLoadPage = $call;
    }

    /**
     * 注册全局配置加载
     * @param callable $call
     */
    public function handleLoadConfig($call)
    {
        $this->callLoadConfig = $call;
    }

    /**
     * 获取客户端类型
     * pc:桌面端
     * phone:移动端
     * @return string
     */
    public function getTerminal()
    {
        return $this->terminal;
    }

    /**
     * 注册回退历史
     * @param callable $call
     */
    public function handleGoBackHistory($call){
        $this->_goBackHistory = $call;
    }

    /**
     * 获取编辑器URL地址
     *
     * @param string $terminal 客户端
     * @return string
     */
    public function getBaseUrl($terminal=null)
    {
        $url = request()->url(true);

        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        $args = array();
        parse_str($query, $args);

        if($terminal){
            $args['terminal'] = $terminal;
        }else{
            unset($args['terminal']);
        }

        $p = strpos($url, '?');
        if ($p !== false) {
            $url = substr($url, 0, $p);
        }
        $p = strpos($url, '#');
        if ($p !== false) {
            $url = substr($url, 0, $p);
        }

        if (count($args)) {
            $url .= "?" . http_build_query($args);
        }

        if ($fragment) {
            $url .= "#" . $fragment;
        }
        return $url;
    }

    /**
     * 获取正在编辑的页面信息
     *
     * @return array
     */
    public function getPage()
    {
        $res = $this->callLoadPage ? call_user_func($this->callLoadPage) : array();
        return short_args($res,
            [
                'page_id' => null,
                'page_title' => "页面",
                'menu_id'=>null,
                'seo_title' => null,
                'seo_keywords' => null,
                'seo_description' => null,
                'content' => null,
                'header' => null,
                'footer' => null,
                'page_metas'=>null,
                'reset_common_header' => null,
                'reset_common_footer' => null
            ]);
    }

    /**
     * 获取编辑器的全局配置信息
     *
     * @return array
     */
    public function getConfig()
    {
        $res = $this->callLoadConfig ? call_user_func($this->callLoadConfig) : array();
        return short_args($res, array(
            'page_width' => 1200,
            'common_header' => "",
            'common_footer' => "",
            'common_metas'=>"",
            'ico'=>"",
            'count_code_baidu_id'=>"",
            'count_code_151_id'=>"",
            'count_code_cnzz_id'=>"",
        ));
    }

    /**
     * 获取已注册的变量集
     * @return array
     */
    public function getAssigns(){
        return $this->callRegisterAssign?call_user_func($this->callRegisterAssign):[];
    }

    /**
     * 获取已注册的filter集
     * @return array
     */
    public function getFilters(){
        return $this->callRegisterFilter?call_user_func($this->callRegisterFilter):[];
    }

    /**
     * 执行后端触发事件
     *
     * @return bool|Response
     * @throws Exception
     */
    public function doEvent()
    {
        if (!request()->isAjax()) {
            return false;
        }

        $_ = request()->param("__");
        if ($_ != $this->getViewKey("event_")) {
            return false;
        }

        $eventKey = request()->param("__event__");
        switch ($eventKey) {
            case 'backstep':   //回退
                if($this->_goBackHistory){
                    return call_user_func($this->_goBackHistory,$this->getPage());
                }

                return successJson();
            case 'init':
                $page = $this->getPage();
                $config =  $this->getConfig();
                if(!$page['header']&&$page['reset_common_header']!=='yes'){
                    $page['header'] = $config['common_header'];
                }

                if(!$page['footer']&&$page['reset_common_footer']!=='yes'){
                    $page['footer'] = $config['common_footer'];
                }
                $config['isHome'] = $this->isHome();

                return successJson(array(
                    'page' => $page,
                    'config' => $config
                ));
            case 'template-cat-list':
                $section_type = request()->param('section_type', '');

                $pageTypes = SectionType::toArray();
                try{
                    return successJson([
                        'title' => isset($pageTypes[$section_type]) ? $pageTypes[$section_type]['label'] : $section_type,
                        'items' => remote_request('api/editor/get-template-cat-list',[
                            'section_type'=>$section_type,
                            'terminal'=>$this->terminal
                        ])
                    ]);
                }catch (\think\Exception $e){
                    return errorJson($e->getMessage());
                }
            case 'template-list':
                try{
                        $response = remote_request('api/editor/get-template-list',[
                            'template_category_id'=> request()->param('template_category_id', ''),
                            'page_index'=> request()->param('page_index', 0, FILTER_VALIDATE_INT)
                        ]);
                }catch (\think\Exception $e){
                    return errorJson($e->getMessage());
                }

                return successJson($response);
            case 'template':
               try{
                   $response = remote_request('api/editor/get-template',[
                       'templateId'=> request()->param('templateId', ''),
                       'terminal'=>$this->terminal
                   ]);
               }catch (\think\Exception $e){
                   return errorJson($e->getMessage());
               }

                return successJson($response);
            case 'parse-template':
                $templates = maybe_json_decode(request()->param('templates'));
                $results = [];

                $assigns = $this->getAssigns();
                $GLOBALS['assigns'] = $assigns;

                foreach ($templates as $template) {
                    $helper = new TwigHelper();
                    try{
                        $results[] = [
                            'domId' => isset($template['domId']) ? $template['domId'] : '',
                            'html' =>$helper->parse($template['template'],$assigns ,$this->getFilters()) //$helper->render($this->getAssigns(),$this->getFilters())
                        ];
                    }catch (Exception $e){
                        if(config('app.app_debug')){
                            throw $e;
                        }
                        Log::error($e);
                        return errorJson(500,'HTML 模板解析失败:请检查语法！');
                    }
                }

                return successJson($results);
            case 'page-update':
                $pageData = inner_args(maybe_json_decode(request()->param('page')), [
                    'menu_id'=>null,
                    'seo_title' => null,
                    'page_metas'=>null,
                    'seo_keywords' => null,
                    'seo_description' => null,
                    'reset_common_header' => 'no',
                    'reset_common_footer' => 'no'
                ]);

                $config= inner_args(maybe_json_decode(request()->param('config')), [
                    'common_header' => "",
                    'common_footer' => "",
                    'common_metas'=>"",

                    'ico'=>"",
                    'count_code_baidu_id'=>"",
                    'count_code_151_id'=>"",
                    'count_code_cnzz_id'=>"",
                ]);

                $page_id = request()->param('page_id', 0, FILTER_VALIDATE_INT);
                $header = base64_decode(request()->param('header'));
                $content = base64_decode(request()->param('content'));
                $footer = base64_decode(request()->param('footer'));
                $pageData['content'] = $content;

                $page = $this->getPage();

                if(!isset($pageData['reset_common_header'])){
                    $pageData['reset_common_header'] = $page['reset_common_header'];
                }
                if(!isset($pageData['reset_common_footer'])){
                    $pageData['reset_common_footer'] = $page['reset_common_footer'];
                }

                if($header){
                    if ($pageData['reset_common_header'] == 'yes') {
                        $pageData['header'] = $header;
                    } else if($header&&($this->isHome()||!defined('APP_DESIGN_LOCK')||!APP_DESIGN_LOCK)) {
                        $pageData['header'] = null;
                        $config['common_header'] = $header;
                    }
                }

                if($header){
                    if ($pageData['reset_common_footer'] == 'yes') {
                        $pageData['footer'] = $footer;
                    } else if($footer&&($this->isHome()||!defined('APP_DESIGN_LOCK')||!APP_DESIGN_LOCK)){
                        $pageData['footer'] = null;
                        $config['common_footer'] = $footer;
                    }
                }

                try {
                    Db::startTrans();
                    if ($this->callUpdate) {
                        call_user_func($this->callUpdate, $page_id,$pageData,$config);
                    }
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    Log::error($e);
                    return errorJson(500, $e->getMessage());
                }
                return successJson();
            case 'save-template':
                $request = parse_args(request()->post(),array(
                    'templateType'=>null,
                    'templateId'=>null,
                    'templateCid'=>null,
                    'pageId'=>null,
                    'html'=>null,
                ));

                $request['html'] = $request['html']?base64_decode($request['html']):'';


                break;
            case 'view':
                $tpl = request()->param('template_name');
                if (empty($tpl)) {
                    return errorJson(404);
                }

                $tpl = preg_replace("/[^a-zA-Z\d\-\_\/]+/", "", $tpl);
                return successJson(
                    fetch("common@editor/xcpc/view/{$tpl}")->getContent()
                );
            case 'json':
                $tpl = request()->param('template_name');
                if (empty($tpl)) {
                    return errorJson(404);
                }

                $tpl = preg_replace("/[^a-zA-Z\d\-\_\/]+/", "", $tpl);
                return successJson(
                    maybe_json_decode(fetch("common@editor/xcpc/json/{$tpl}")->getContent(), true)
                );
            case 'search-page':
//                $keywords = trim(request()->param('keywords', ''));
//                if ($this->handleSearchPage) {
//                    return call_user_func($this->handleSearchPage, $keywords);
//                }

                return json(array(
                    'items' => null
                ));
        }
    }

    /**
     * 判断当前编辑页面是否是首页
     * @return boolean
     */
    public function isHome(){
        return call_user_func($this->callIsHome);
    }

    /**
     * 注册首页判断
     * @param callable $call
     */
    public function handleIsHome($call){
        $this->callIsHome = $call;
    }

    /**
     * 获取退出编辑器跳转页面
     * @return string
     */
    public function getBackUrl()
    {
        return empty($this->back_url) ? url('sponsor/admin.home/index') : $this->back_url;
    }

    /**
     * 加载模板输出
     * @access protected
     * @param  string $template 模板文件名
     * @param  array $vars 模板输出变量
     * @param  array $config 模板参数
     * @return Response
     * @throws Exception
     */
    function fetch($template = 'common@editor/xcpc/index', $vars = [], $config = [])
    {
        $this->assign($vars);

        $res = $this->doEvent();
        if ($res instanceof Response) {
            return $res;
        }

        return Response::create($template, 'view')->assign($vars)->config($config);
    }
}