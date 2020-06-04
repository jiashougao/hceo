<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/6/1
 * Time: 10:21
 */

namespace app\product\controller;


use think\Controller;

class Detail extends Controller
{

    /*
     *产品详情
     */
    public function detail(){




        //商品详情
        $parameter = http_build_query([
            'product'        => isset($_GET['id'])?$_GET['id']:1,
        ]);
        $url1 = 'web/product/detail?'.$parameter;
        $product = [];
        $product = http_get($url1,false);


        //商户详情
        $supplier = [];
        if ($product['supplier_id']){
            //商品详情
            $parameter2 = http_build_query([
                'supplier'        => $product['supplier_id'],
            ]);
            $url2 = 'web/supplier/detail?'.$parameter2;
            $supplier = http_get($url2,false);
            $logo = maybe_json_decode($supplier['logo']);
            $supplier['logo'] = esc_img_url($logo);
        }


        $product['albums'] = [];
        if (isset($product['album'])){
            $product['albums'] = maybe_json_decode($product['album']);

        }
        //$product['detail'] = htmlspecialchars($product['detail']);
        dump($product);
        //推荐商品
        $this->assign([
            'product'  =>$product,
            'supplier' =>$supplier,
        ]);


        return $this->fetch('product_details');

    }


    /**
     *
     */
    public function request(){


    }






}