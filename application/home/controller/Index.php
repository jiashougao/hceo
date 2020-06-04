<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/6/1
 * Time: 10:22
 */

namespace app\home\controller;


use think\Controller;

class Index extends Controller
{
    public function index(){

        $news = http_get('web/article/article_index');
        $this->assign('news', $news);
        return $this->fetch();
    }
}