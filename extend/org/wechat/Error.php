<?php
namespace org\wechat;

use think\Exception;

class Error{

    /**
     * @var Token
     */
    private $token;
    private $response;

    /**
     * Error constructor.
     * @param $token Token
     * @param $response
     */
    public function __construct($token,$response){
        $this ->token =$token;
        $this->response = $response;
    }

    /**
     * @return mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get(){
        $obj = maybe_json_decode($this->response,true);
        if(!$obj){
            return $this->response;
        }
        
        if(isset($obj['errcode'])){
            switch ($obj['errcode']){
                case 0:
                    return $obj;
                    //获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口
                case 40002:
                    //不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写
                case 40125:
                    //不合法的appsecret
                case 40013:
                     //ip白名单
                case 40164:
                    //不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID
                case 40003: 
                case 40132:
                case 41001:
				//公众号未认证
				case 48001:
                    throw new Exception($this->response,500);
                    //access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明
                case 42001:
                    //不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口
                case 40014:
                    //缺少access_token参数
                    //获取 access_token 时 AppSecret 错误，或者 access_token 无效。请开发者认真比对 AppSecret 的正确性
                case 40001:
                    $this->token->accessToken(true);
                    //不合法的凭证类型
                    throw new Exception($this->response,40001);
                default:
                    throw new Exception($this->response,$obj['errcode']);
            }
        }
        
        return $obj;
    } 
}