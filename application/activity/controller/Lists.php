<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/3
 * Time: 18:02
 */

namespace app\activity\controller;


use think\Controller;

class Lists extends Controller
{

    /**
     * 新闻列表
     * don
     */
    public function lists(){
        $parameter = [];
        $url = '/web/activity/activity_list';
        $data = http_post($url,$parameter);


        $list = [];
        if (isset($data['data']['data'])){
            $list = $data['data']['data'];
        }
        $this->assign([
            'list' =>$list
        ]);
        return $this->fetch('activity_list');
    }

}