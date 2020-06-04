<?php

namespace org\wechat\handler\event;
use org\wechat\handler\BaseHandler;
use org\wechat\Response;
use org\wechat\User;
use org\wechat\Tpl;
use PDO;
use think\Exception;
use think\facade\Log;

abstract class BaseEventHandler extends BaseHandler
{
    /**
     * @param $request
     * @throws Exception
     */
    protected function handleAuth($request){
        $queue_id = $request['EventKey'];
        $openid = $request['FromUserName'];

        if(strpos($queue_id, 'qrscene_')===0){
            $queue_id = substr($queue_id,8 );
        }

        if(empty($queue_id)){return false;}

        $handler = new User($this->token);
        $user = $handler->getByOpenid($openid);
        Log::error(print_r($user,true));
        $obj = db_default('module_auth')->where(array(
            'id'=>$queue_id,
            'status'=>'prepare'
        ))->find();
        if($obj){
            db_default('module_auth')->where(array(
                    'id'=>$queue_id,
                    'status'=>'prepare'
                ))
                ->update(array(
                    'wechat_user_info'=>json_encode($user,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
                    'status'=>'publish'
                ));
            try{
                $tpl = new Tpl($this->token);
                $data = [
                    'keyword1' => array(
                        'value' => $user['nickname']
                    ),
                    'keyword2' => array(
                        'value' => date('Y-m-d H:i:s')
                    ),
                ];

                $templateId = db_default('module_tpl_wxtpl')->where('appid',$this->token->appid)->where('short_template_id','OPENTM408239664')->value('template_id');

                $tpl->sendTemplate($openid,$templateId,"","",json_decode(json_encode($data),false));
            }catch (Exception $e){
                Log::error($e);
            }
//            $response = new Response($request);
//            return $response->responseText("感谢，欢迎登陆！");
        }
        return false;
    }
}