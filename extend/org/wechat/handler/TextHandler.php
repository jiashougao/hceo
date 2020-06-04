<?php

namespace org\wechat\handler;

use org\wechat\CustomerService;
use org\wechat\Response;

class TextHandler extends BaseHandler
{
    public function handle($request)
    {
       $content = !empty($request['Content'])?$request['Content']:null;
       //if(strpos($content,'小程序')!==false){
           //$response = new CustomerService($this->token);
           // $response->sendContent($request['FromUserName'],"感谢您关注我们！\r\n点击<a href='http://www.qq.com' data-miniprogram-appid='wx50273c0d39fb241e' data-miniprogram-path='pages/index/index'>Blum 百隆家具五金</a>获取更多活动信息");
       //}
    }
}