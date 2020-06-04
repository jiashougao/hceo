<?php
namespace org\wechat;
use org\helper\FileHelper;
use org\helper\HttpHelper;
use org\helper\UrlHelper;
use org\qiniu\UploadHelper;
use think\Exception;
use think\facade\Log;

class Menu{
    /**
     * @var Token
     */
    private $token;
    public function __construct($token){
       $this ->token = $token;
    }

    /**
     * 同步微信菜单
     * @param $menus
     * @param int $stop
     * @return array
     * @throws Exception
     */
    public function syncService($menus,$stop=0){
        try{
            $access_token = $this->token->accessToken();
            $response = HttpHelper::POST("https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}",json_encode(
                array(
                    'button'=>$menus
                ),
                JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
            ));
            $error = new Error($this ->token,$response);
            return $error->get();
        }catch (\Exception $e){
            if($e->getCode()===40001&&$stop<=2){
                return $this->syncService($menus,$stop+1);
            }
            throw $e;
        }

    }
}