<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/6/1
 * Time: 10:21
 */

namespace app\supplier\controller;


use think\Controller;
use think\Exception;

class Lists extends Controller
{

    /**
     * @return mixed
     * 商户列表首页
     * don
     */
    public function index(){



            //左侧搜索分页数据
            $is_cache_search    =   false;          //是否启用get缓存
            $search['type']     =  'search';
            $search['order'] = 'booth_no:asc';

            //排序
            if (isset($_GET['order'])){
                //展位号
                if ($_GET['order']   == 'boPo'){
                    $search['order'] = 'booth_no:asc';
                }
                if ($_GET['order']   == 'boRe'){
                    $search['order'] = 'booth_no:desc';
                }
                //首字母
                if ($_GET['order']   == 'initials'){
                    $search['order'] = 'name_alpha:asc';
                }
                //热度
                if ($_GET['order']   == 'hot'){
                    $search['order'] = 'view_count:desc';
                }
            }

            //搜索
            if (isset($_GET['search'])){
                $is_cache_search = false;
                if (!empty($_GET['search'])){
                    $search['keywords'] = $_GET['search'];
                }

            }

            $parameter_search = http_build_query($search);
            $url1 = 'web/supplier/list?'.$parameter_search;


            $data_search = http_get($url1,$is_cache_search);

            $datasearch  = [];
            if (isset($data_search['items'])){
                $datasearch = $data_search['items'];
            }

            //右侧推荐展商数据
            $parameter_recommend = http_build_query([
                'type'        => 'recommend',
                'order'       => 'booth_no:asc',
            ]);
            $url2 = 'web/supplier/list?'.$parameter_recommend;
            $data_recommend = http_get($url2,false);
            $datarecommend  = [];
            if (isset($data_recommend['items'])){
                $datarecommend = $data_recommend['items'];
            }


            $this->assign([
                'data_search'    =>$datasearch,
                'data_recommend' =>$datarecommend
            ]);
            return $this->fetch();




    }

    /**
     * ajax请求
     */
    public function request(){
        

    }



}