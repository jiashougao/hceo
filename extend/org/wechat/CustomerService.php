<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/1/13
 * Time: 15:02
 */

namespace org\wechat;


use Exception;
use org\helper\HttpHelper;

class CustomerService
{
    /**
     * @var Token
     */
    private $token;
    public function __construct($token){
        $this ->token = $token;
    }

    /**
     * @param $openid
     * @param $msg
     * @param int $stop
     * @return bool|mixed|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function send($openid,$msg,$stop=0){
        try{
            $access_token = $this->token->accessToken();
            $response = HttpHelper::POST("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}",json_encode($msg,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
            $error = new Error($this ->token,$response);
            $response =  $error->get();
            return $response;
        }catch (\Exception $e){
            if($e->getCode()===40001&&$stop<=2){
                return $this->send($openid,$msg,$stop+1);
            }
            throw $e;
        }
    }

    /**
     * 发送文本消息
     * @param $openid
     * @param $content
     * @return bool|mixed|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sendContent($openid,$content){
        return $this->send($openid,array(
            "touser"=>$openid,
            "msgtype"=>"text",
            "text"=>array(
               'content'=>$content
            )
        ));
    }

    /**
     * 发送小程序
     *
     * @param $openid
     * @param $title
     * @param $appid
     * @param $apppath
     * @param $thumb_media_id
     * @return bool|mixed|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sendMiniProgram($openid,$title,$appid,$apppath,$thumb_media_id){
        return $this->send($openid,array(
            "touser"=>$openid,
            "msgtype"=>"miniprogrampage",
            "miniprogrampage"=>
            array(
                "title"=>$title,
                "appid"=>$appid,
                "pagepath"=>$apppath,
                "thumb_media_id"=>$thumb_media_id
            )
        ));
    }
}