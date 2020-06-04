<?php


namespace org\wechat;


use org\helper\HttpHelper;
use think\Exception;

class Tpl
{
    /**
     * @var Token
     */
    private $token;

    /**
     * Tpl constructor.
     * @param Token $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @param string $short_templateId 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     * @param bool $refresh 强制刷新
     * @param int $stop
     * @return string
     * @throws Exception
     */
    public function createTemplate($short_templateId,$refresh = false, $stop=0){
        try{
            $cache = db_default('module_tpl_wxtpl')
                ->where(array(
                    'short_template_id'=>$short_templateId,
                    'appid'=>$this->token->appid
                ))
                ->find();
            if(!$refresh){
                if($cache&&!empty($cache['template_id'])){
                    return $cache['template_id'];
                }
            }

            $access_token = $this->token->accessToken();
            $response = HttpHelper::POST("https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token={$access_token}",json_encode(array(
                'template_id_short'=>$short_templateId
            ),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

            $error = new Error( $this->token,$response);
            $response = $error->get();
            if(empty($response['template_id'])){
                throw new Exception('微信消息模板创建失败！');
            }

            if($cache){
                db_default('module_tpl_wxtpl')
                    ->where(array(
                        'short_template_id'=>$short_templateId,
                        'appid'=>$this->token->appid
                    ))
                    ->update(array(
                        'template_id'=>$response['template_id']
                    ));
            }else{
                db_default('module_tpl_wxtpl')
                    ->insert(array(
                        'short_template_id'=>$short_templateId,
                        'appid'=>$this->token->appid,
                        'template_id'=>$response['template_id'],
                        'created_time'=>date('Y-m-d H:i:s')
                    ));
            }

            return $response['template_id'];
        }catch (\Exception $e){
            if($e->getCode()===40001&&$stop<=2){
                return $this->createTemplate($short_templateId,$refresh,$stop+1);
            }
            throw $e;
        }
    }

    /**
     * 获取所有模板
     * @param string $templateId 公众帐号下模板消息ID
     * @return mixed
     * @throws Exception
     */
    public function getTemplateById($templateId){
        $templates = $this->getTemplates();
        foreach ($templates as $template){
            if($template['template_id']===$templateId){
                return $template;
            }
        }

        return null;
    }

    /**
     * 获取消息模板ID
     *
     * @param string $short_templateId 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     * @param bool $refresh 刷新缓存
     * @return string
     * @throws Exception
     */
    public function getTemplateIdByShortId($short_templateId,$refresh=false){
        if(empty($short_templateId)){
            throw new Exception('short template id is empty');
        }

        return $this->createTemplate($short_templateId,$refresh);
    }

    /**
     * 获取所有模板
     * @param string $short_templateId 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     * @param bool $refresh 强制刷新模板
     * @return mixed
     * @throws Exception
     */
    public function getTemplateByShortId($short_templateId,$refresh = false){
        if(empty($short_templateId)){
            throw new Exception('short template id is empty');
        }

        $templateId = $this->createTemplate($short_templateId,$refresh);
        $templates = $this->getTemplates();
        foreach ($templates as $template){
            if($template['template_id']===$templateId){
                return $template;
            }
        }

        return null;
    }

    /**
     * 获取所有模板
     * @param int $stop
     * @return mixed
     * @throws Exception
     */
    public function getTemplates($stop=0){
        try{
            $access_token = $this->token->accessToken();
            $response = HttpHelper::GET("https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token={$access_token}");

            $error = new Error( $this->token,$response);
            $response = $error->get();
            return isset($response['template_list'])
                &&is_array($response['template_list'])
                &&count($response['template_list'])
                ?$response['template_list']:array();

        }catch (\Exception $e){
            if($e->getCode()===40001&&$stop<=2){
                return $this->getTemplates($stop+1);
            }
            throw $e;
        }
    }

    /**
     * @param string $templateId 公众帐号下模板消息ID
     * @param int $stop
     * @return array
     * @throws Exception
     */
    public function removeTemplate($templateId, $stop=0){
        try{
            $access_token = $this->token->accessToken();
            $response = HttpHelper::POST("https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token={$access_token}",json_encode(array(
                'template_id'=>$templateId
            )));

            $error = new Error( $this->token,$response);
            $response =  $error->get();
            db_default('module_tpl_wxtpl')
                ->where(array(
                    'template_id'=>$templateId,
                    'appid'=>$this->token->appid
                ))->delete();
            return $response;
        }catch (\Exception $e){
            if($e->getCode()===40001&&$stop<=2){
                return $this->removeTemplate($templateId,$stop+1);
            }
            throw $e;
        }
    }

    /**
     * 发送模板消息
     * @param string $openid 用户openid
     * @param string $templateId 消息模板ID
     * @param string $url 消息跳转的链接
     * @param array $miniprogram 跳转小程序
     *          array(
     *              "appid":"xiaochengxuappid12345",
                    "pagepath":"index?foo=bar"
     *           )
     * @param array $data
     *          array(
                    "first"=>array(
                        "value"=>"恭喜你购买成功！",
                        "color"=>"#173177"
                    ) ,
                   "keyword1"=>array(
                        "value"=>"test",
                        "color"=>"#173177"
                   )
     *            ...
     *          )
     * @param int $stop
     * @return array
     * @throws Exception
     */
    public function sendTemplate($openid,$templateId,$url=null,$miniprogram=array(),$data=array(),$stop=0){
        $request = array(
            'touser'=>$openid,
            'template_id'=>$templateId,
            'data'=>$data
        );
        if($url){
            $request['url'] = $url;
        }
        if($miniprogram&&count($miniprogram)){
            $request['miniprogram'] = $miniprogram;
        }
        try{
            $access_token = $this->token->accessToken();
            $response = HttpHelper::POST("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}",json_encode($request,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

            $error = new Error( $this->token,$response);
            return $error->get();
        }catch (\Exception $e){
            if($e->getCode()===40001&&$stop<=2){
                return $this->sendTemplate($openid,$templateId,$url,$miniprogram,$data,$stop+1);
            }
            throw $e;
        }
    }
}