<?php
namespace org\helper;
use think\Exception;

/**
 * 微信消息模板操作类
 *
 * @since v1.0.0
 * @author ranj
 * Class WechatTemplateMsgHelper
 * @package org\helper
 */
class WechatTemplateMsgHelper{
    /**
     * @param $openids string|array 微信用户ID
     * @param $tplCode string 模板别名
     * @param null $url
     * @param array $miniprogram
     * @param array $data
     * @throws Exception
     */
    public function send($openids,$tplCode,$url=null,$miniprogram=array(),$data=array()){
        $request = [
            'openids'=>$openids,
            'tplCode'=>$tplCode,
            'url'=>$url,
            'miniprogram'=>$miniprogram,
            'data'=>$data
        ];
        remote_request('api/tpl/sendwxtemplate',$request);
    }
}