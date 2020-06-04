<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/3
 * Time: 14:45
 */

namespace app\news\controller;


use think\Controller;

class Detail extends Controller
{

    /**
     * 新闻详情
     * don
     */
    public function detail(){

        $parameter = http_build_query([
            'id'                => isset($_GET['id'])?$_GET['id']:1,
            'supplier_id'       => isset($_GET['supplier_id'])?$_GET['supplier_id']:1,
        ]);
        $url = '/web/article/article_detail?'.$parameter;
        $data = http_get($url,false);
        $data['content'] = '';
        if (isset($data['detail'])){
            $deatil = maybe_json_decode($data['detail']);
            if (isset($deatil['content'])){
                $data['content'] = $deatil['content'];
            }
        }
        /*if (empty($data)){
            $this->redirect('/404.html');
        }*/
        $this->assign([
            'data' =>$data
        ]);
        return $this->fetch('news_detail');


    }







}