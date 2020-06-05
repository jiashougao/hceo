<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/6/3
 * Time: 14:45
 */

namespace app\news\controller;

use think\Controller;

class Lists extends Controller
{

    /**
     * 新闻列表
     * don
     */
    public function lists(){


        exit('fdfd');
        $parameter = [];
        $url = '/web/article/article_list';
        $data = http_post($url,$parameter);


        $list = [];
        if (isset($data['data']['data'])){
            $list = $data['data']['data'];
        }
        $this->assign([
            'list' =>$list
        ]);

        return $this->fetch('news_lists');

    }


}