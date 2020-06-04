<?php
namespace org\helper;

use think\facade\Cookie;

/**
 * 用户ID ，经过加密算法生成hash，存储到cookie
 *
 * server泛解析hash 获得ID，绑定用户信息
 *
 * Class AuthHelper
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class AuthHelper{
    private $core;

    public function __construct($core)
    {
        $this->core = $core;
        $config = [
            'prefix'=>'auth-',
        ];

        $config['domain'] =  get_root_domain();

        Cookie::init($config);
    }

    /**
     * 判断cookie中是否已存储合法的加密数据
     * @return bool
     */
    public function is_authorized() {
        return $this->getAuthCookie()!=null;
    }

    /**
     * 用户ID ，加密算法后，存储到cookie中
     *
     * @param $user_id
     * @param bool $remember
     */
    public function setAuthCookie($user_id, $remember = true) {
        $data =$user_id;

        $salt = config('app.auth_salt');
        $key = hash_hmac('md5', $data, $salt);
        $hash = hash_hmac('md5', $data, $key);

        $data =$user_id.'|'.$hash;

        Cookie::set($this->core,urlencode($data),[
            'expire'=>$remember?(time () + 60*2):null,
        ]);
    }

    /**
     * 清除身份识别相关的cookie数据
     */
    public function clearAuthCookie(){
        Cookie::delete($this->core);
    }

    /**
     * 从cookie中获取hash，泛解析获取到用户ID
     *
     * @return array
     */
    public function getAuthCookie() {
        $cookie_key = $this->core;
        $cookie = Cookie::get($cookie_key);
        if(empty($cookie)){
            return null;
        }

        $hash_str = explode('|',urldecode($cookie),2);
        if(count($hash_str)!=2){
            return null;
        }

        $data =$hash_str[0];
        $salt = config('app.auth_salt');
        $key = hash_hmac('md5', $data, $salt);
        $hash = hash_hmac('md5', $data, $key);
        if($hash!=$hash_str[1]){
            return null;
        }

        return $data;
    }
}