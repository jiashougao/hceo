<?php
namespace org\wechat;

use org\wechat\hash\WXBizMsgCrypt;
use think\Exception;
class Hash{
    private $encodingAesKey,$encoding_token;
    /**
     * @var Token
     */
    private $token;

    /**
     * Hash constructor.
     * @param Token $token
     * @param $encoding_token
     * @param $encodingAesKey
     */
    public function __construct($token,$encoding_token,$encodingAesKey)
    {
        $this->encodingAesKey = $encodingAesKey;
        $this->encoding_token = $encoding_token;
        $this->token = $token;
    }

    /**
     * @param $xml
     * @return array|null
     * @throws Exception
     */
    public function decrypt($xml)
    {
        $encrypt_type = (isset($_GET['encrypt_type']) && ($_GET['encrypt_type'] == 'aes')) ? "aes" : "raw";
        $msg = '';
        
        if ($encrypt_type == 'aes') {
            if(!function_exists('openssl_decrypt')){
                throw new Exception('PHP openssl缺失，导致解密微信推送失败');
            }

            $pc = new WXBizMsgCrypt($this->encoding_token, $this->encodingAesKey, $this->token->appid);
            $errCode = $pc->decryptMsg($_GET['msg_signature'], $_GET['timestamp'], $_GET['nonce'], $xml, $msg);
            if ($errCode != 0) {
                return null;
            }
        } else {
            $msg = $xml;
        }
        
        return get_object_vars(simplexml_load_string($msg, 'SimpleXMLElement', LIBXML_NOCDATA));
    }
}