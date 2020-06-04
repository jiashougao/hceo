<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/6/1
 * Time: 10:21
 */

namespace app\supplier\controller;


use think\Controller;

class details extends Controller
{
    /**
     * 商户详情页
     * @return mixed
     * @author LiuHuan 2020/06/02
     */
    public function index(){
        //判断是PC端还是移动端
        $terminal = is_terminal_mobile();
        $id = 1;

        //获取商户下的产品列表
        $products = http_get('web/product/list?type=supplier_detail&page=1&page_size=1&supplier='.$id.'&fetch_count=yes',false);



        //获取商户的基本信息
        $supplier = http_get('web/supplier/detail?supplier='.$id);

        //分页数据
        $count = isset($products['count'])?$products['count']:0;
        if($terminal=="pc"){
            $page_html = pc_page(1,$count,1,'web/product/list');
        }else{
            $page_html = mobile_page('web/product/list');
        }
        $this->assign([
            'page_html' => $page_html,
            'id'=>$id,
            'products'=>isset($products['items']) ? $products['items']:[],
            'supplier'=>$supplier
        ]);
        return $this->fetch();
    }
}