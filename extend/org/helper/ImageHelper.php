<?php
namespace org\helper;
use Endroid\QrCode\QrCode;
use think\Exception;

/**
 * 图片相关
 *
 * Class ImageHelper
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class ImageHelper{
    public static function qrcode($data){
//        if(!$data){
//            return null;
//        }
//        $qrCode = new QrCode($data);
//        if($img){
//            return "data:image/png;base64,".base64_encode($qrCode->writeString());
//        }
//        return $qrCode;
        return url("module/web.func/qrcode",["data"=>base64_encode($data)],true,true);
    }
}