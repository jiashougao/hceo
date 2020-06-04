<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/1/13
 * Time: 15:19
 */

namespace org\wechat;

/**
 * 微信临时素材
 * Class MaterialTemp
 * @package org\wechat
 */
class MaterialTemp
{
    /**
     * @var Token
     */
    private $token;
    public function __construct($token){
        $this ->token = $token;
    }

}