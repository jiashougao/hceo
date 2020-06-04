<?php
namespace org\wechat;

class CustomToken extends Token {
    /**
     * Token constructor.
     * @param string $appid
     * @param string $appsecret
     * @throws \think\Exception
     */
    public function __construct($appid,$appsecret){
        parent::__construct(null);
        $this->appid  = $appid;
        $this->appsecret = $appsecret;
    }
}