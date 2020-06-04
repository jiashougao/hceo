<?php
namespace org\wechat;

use org\helper\HttpHelper;
use think\Exception;

class Qrcode{
    /**
     * @var Token
     */
    private $token;
    public function __construct($token){
        $this ->token = $token;
    }

    /**
     * @param $queue_id
     * @param int $expire_seconds
     * @param int $stop
     * @return array
     * @throws Exception
     */
    public function create($queue_id,$expire_seconds=900,$stop=0){
        if($queue_id<=0){
            throw new Exception('invalid queue_id!',500);
        }

        try{
            $access_token = $this->token->accessToken();
            $response =HttpHelper::POST(
                "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={$access_token}",
                json_encode(array(
                    'expire_seconds'=>$expire_seconds,//过期时间
                    'action_name'=>'QR_STR_SCENE',
                    'action_info'=>array(
                        'scene'=>array(
                            'scene_str'=>$queue_id
                        )
                    )
                ),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

            $error = new Error($this ->token,$response);
            $obj = $error->get();
            return array(
                'queue_id'=>$queue_id,
                'url'=>"https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($obj['ticket'])
            );
        }catch (\Exception $e){
            if($e->getCode()===40001&&$stop<=2){
                return $this->create($queue_id,$expire_seconds,$stop+1);
            }
            throw $e;
        }
    }
}