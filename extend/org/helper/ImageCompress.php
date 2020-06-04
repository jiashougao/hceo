<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/2/9
 * Time: 22:12
 */

namespace org\helper;

use think\Exception;

/**
 * 图片压缩操作
 *
 * Class ImageCompress
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class ImageCompress{

    private $src;
    private $image;
    private $imageInfo;
    private $percent = 0.5;

    /**
     * 图片压缩
     * @param  string$src 源图
     * @param int $percent 压缩比例
     */
    public function __construct($src, $percent=1)
    {
        $this->src = $src;
        $this->percent = $percent;
    }

    /** 高清压缩图片
     * @param string $saveName 提供图片名（可不带扩展名，用源图扩展名）用于保存。或不提供文件名直接显示
     * @throws Exception
     */
    public function compress($saveName)
    {
        $this->_openImage();
        $this->_saveImage($saveName);
        $this->destroy();
    }

    /**
     * 内部：打开图片
     * @throws Exception
     */
    private function _openImage()
    {
        $info = @getimagesize($this->src);
        if(!$info){
            throw new Exception("unknown image source!");
        }
        list($width, $height, $type, $attr) = $info;
        $this->imageInfo = array(
            'width'=>$width,
            'height'=>$height,
            'type'=>@image_type_to_extension($type,false),
            'attr'=>$attr
        );

        $fun = "imagecreatefrom".$this->imageInfo['type'];
        $this->image = @$fun($this->src);
        if(!$this->image){
            throw new Exception("unknown image source!");
        }
        $this->_thumpImage();
    }

    /**
     * 内部：操作图片
     * @throws Exception
     */
    private function _thumpImage()
    {
        $new_width = $this->imageInfo['width'] * $this->percent;
        $new_height = $this->imageInfo['height'] * $this->percent;
        $image_thump = @imagecreatetruecolor($new_width,$new_height);
        if(!$image_thump){
            throw new Exception("image create true color failed!");
        }
        //将原图复制带图片载体上面，并且按照一定比例压缩,极大的保持了清晰度
        if(!@imagecopyresampled($image_thump,$this->image,0,0,0,0,$new_width,$new_height,$this->imageInfo['width'],$this->imageInfo['height'])){
            throw new Exception("image copy re sampled failed!");
        }

        if(!@imagedestroy($this->image)){
            throw new Exception("image destroy failed!");
        }

        $this->image = $image_thump;
    }

    /**
     * 保存图片到硬盘：
     * @param  string $dstImgName 1、可指定字符串不带后缀的名称，使用源图扩展名 。2、直接指定目标图片名带扩展名。
     * @throws Exception
     */
    private function _saveImage($dstImgName)
    {
        if(empty($dstImgName)) return;

        $allowImageExtList = ['.jpg', '.jpeg', '.png', '.bmp', '.wbmp','.gif'];   //如果目标图片名有后缀就用目标图片扩展名 后缀，如果没有，则用源图的扩展名
        $dstExt =  strrchr($dstImgName ,".");
        $sourceExt = strrchr($this->src ,".");
        if(!empty($dstExt)) $dstExt =strtolower($dstExt);
        if(!empty($sourceExt)) $sourceExt =strtolower($sourceExt);

        //有指定目标名扩展名
        if(!empty($dstExt) && in_array($dstExt,$allowImageExtList)){
            $dstName = $dstImgName;
        }elseif(!empty($sourceExt) && in_array($sourceExt,$allowImageExtList)){
            $dstName = $dstImgName.$sourceExt;
        }else{
            $dstName = $dstImgName.$this->imageInfo['type'];
        }

        if(!$this->image){
            throw new Exception("save image failed!");
        }

        $funcs = "image".$this->imageInfo['type'];
        if(!@$funcs($this->image,$dstName)){
            throw new Exception("save image failed!");
        }
    }

    /**
     * 销毁图片
     */
    public function destroy(){
        if($this->image){
            imagedestroy($this->image);
            $this->image=null;
        }
    }
}