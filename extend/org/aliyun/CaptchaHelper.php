<?php
namespace org\aliyun;
use app\common\service\PluginService;
use Emmetltd\AliyunCore\Api\afs\Request\V20180112\AuthenticateSigRequest;
use Emmetltd\AliyunCore\DefaultAcsClient;
use Emmetltd\AliyunCore\Profile\DefaultProfile;

/**
 * 阿里云 拖动验证码
 * Class CaptchaHelper
 * @since v1.0.0
 * @author ranj
 * @package org\aliyun
 */
class CaptchaHelper
{
    /**
     * 验证码server端数据验证
     *
     * @param string $session_id
     * @param string $token
     * @param string $sig
     * @return bool
     * @throws \think\Exception
     */
    public static function validate($session_id,$token,$sig){
        $aliyun_access_key = PluginService::getConfig('aliyun_access_key');
        $aliyun_access_secret =  PluginService::getConfig('aliyun_access_secret');
        $aliyun_captcha_appkey =  PluginService::getConfig('aliyun_captcha_hd_h5_appkey');
        $aliyun_captcha_scene =  PluginService::getConfig('aliyun_captcha_hd_h5_scene');

        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $aliyun_access_key, $aliyun_access_secret);
        $client = new DefaultAcsClient($iClientProfile);
        DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", "afs", "afs.aliyuncs.com");

        $request = new AuthenticateSigRequest();
        $request->setSessionId($session_id);// 会话ID。必填参数，从前端获取，不可更改。
        $request->setToken($token);// 请求唯一表示。必填参数，从前端获取，不可更改。
        $request->setSig($sig);// 签名串。必填参数，从前端获取，不可更改。
        $request->setScene($aliyun_captcha_scene);// 场景标识。必填参数，从前端获取，不可更改。
        $request->setAppKey($aliyun_captcha_appkey);// 应用类型标识。必填参数，后端填写。
        $request->setRemoteIp(get_client_ip());// 客户端IP。必填参数，后端填写。
        $response = $client->getAcsResponse($request);// 返回code 100表示验签通过，900表示验签失败
        return $response&&isset($response->Code)&&$response->Code==100;
    }
}