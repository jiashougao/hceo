<?php
namespace org\qiniu;

use app\common\model\OptionHelper;
use app\common\service\PluginService;
use org\helper\FileHelper;
use org\helper\HttpHelper;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use think\Exception;
use think\facade\Env;

/**
 * 七牛云文件上传操作
 *
 * @since v1.0.0
 * @author ranj
 * Class UploadHelper
 * @package org\qiniu
 */
class UploadHelper
{

    const DIR_WECHAT_HEADE = 'wechat/headv2/';
    const DIR_WECHAT_HEADE_PREG = 'wechat\/headv2\/';
    /**
     * @var array|bool
     */
    public $secretkey;
    /**
     * @var array|bool
     */
    public $accesskey;
    /**
     * @var array|bool
     */
    public $bucket;
    /**
     * @var array|bool
     */
    public $domain;

    public function __construct()
    {
        $this->accesskey = PluginService::getConfig ('qiniuyun_accesskey');
        $this->secretkey = PluginService::getConfig ('qiniuyun_secretkey');
        $this->bucket = PluginService::getConfig ('qiniuyun_bucket');
        $this->domain = PluginService::getConfig ('qiniuyun_domain');
    }

    /**
     * 删除
     * @param $fileKey
     * @throws Exception
     */
    public function remove($fileKey){
        $config = new Config();
        $auth = new Auth($this->accesskey, $this->secretkey);
        $upManager = new BucketManager($auth,$config);

        list($ret, $error) = $upManager->delete($this->bucket,$fileKey);
        if ($error&&$error->code()) {
            throw new Exception($error->message());
        }
    }

    /**
     * 获取已存储文件的信息
     *
     * @param $fileKey
     * @return array|bool
     */
    public function stat($fileKey){
        $config = new Config();
        $auth = new Auth($this->accesskey, $this->secretkey);
        $upManager = new BucketManager($auth,$config);

        list($ret, $error) = $upManager->stat($this->bucket,$fileKey);
        if ($error) {
           return false;
        }
        return $ret;
    }

    /**
     * 文件上传
     *
     * @param string $fileKey 文件名
     * @param string $filePath 文件地址
     * @return string
     * @throws Exception
     */
    public function upload($fileKey,$filePath){
        $upManager = new UploadManager();
        $auth = new Auth($this->accesskey, $this->secretkey);
        $token = $auth->uploadToken($this->bucket);

        list($ret, $error) = $upManager->putFile($token,$fileKey,$filePath);
        if ($error) {
            throw new Exception($error->message());
        }

        return $this->domain.'/'.$fileKey;
    }

    public static function isCachedHeadImage($headImgUrl){
        $imgHelper = new UploadHelper();
        return strpos($headImgUrl,$imgHelper->domain)===0;
    }

    /**
     * 获取微信用户头像本地地址
     *
     * @param string $headImgUrl
     * @param string $date
     * @return string
     */
    public static function getHeadImageUrlLocal($headImgUrl,$date){
        $baseDir =  '/upload/';
        $fileDir = "images/" . date('Y/m/',strtotime($date));
        $cloudDir = self::DIR_WECHAT_HEADE;
        return preg_replace(
            "/^.+\/".self::DIR_WECHAT_HEADE_PREG."/i",
            request()->scheme() . '://' .request()->host().$baseDir.$fileDir.$cloudDir,
            $headImgUrl
        );
    }
    /**
     * 保存微信客户头像到本地和云存储
     * @param $headImgUrl
     * @param string $date
     * @return array
     * @throws Exception
     */
    public static function saveHeadImageUrl($headImgUrl,$date){
        $rootDir = Env::get('root_path'). 'public';
        $baseDir =  '/upload/';
        $fileDir = "images/" . date('Y/m/',strtotime($date));
        $cloudDir = self::DIR_WECHAT_HEADE;
        $fileName = md5($headImgUrl).'.png';

        FileHelper::make_writeable_dir($rootDir.$baseDir.$fileDir);
        if(!@file_exists($rootDir.$baseDir.$fileDir.$fileName)){
            $response = HttpHelper::GET($headImgUrl,false,5);
            if(!$response||!@file_put_contents($rootDir.$baseDir.$fileDir.$fileName, $response)){
                throw new Exception('远程文件保存失败！');
            }
        }

        $url = null;
        if( OptionHelper::get('enable_img_cloud','yes')!=='yes'){
            return array(
                'url'=>request()->scheme() . '://' .request()->host().$baseDir.$fileDir.$fileName,
                'url_local'=>request()->scheme() . '://' .request()->host().$baseDir.$fileDir.$fileName,
            );
        }

        $imgHelper = new UploadHelper();
        if(self::isCachedHeadImage($headImgUrl)){
            return array(
                'url'=>$headImgUrl,
                'url_local'=>request()->scheme() . '://' .request()->host().$baseDir.$fileDir.$fileName,
            );
        }

        //判断头像是否存在
        if(!$imgHelper->stat($cloudDir.$fileName)){
            return array(
                'url'=>$imgHelper->upload($cloudDir.$fileName,$rootDir.$baseDir.$fileDir.$fileName),
                'url_local'=>request()->scheme() . '://' .request()->host().$baseDir.$fileDir.$fileName,
            );
        }

        return array(
            'url'=>$imgHelper->domain.'/'.$cloudDir.$fileName,
            'url_local'=>request()->scheme() . '://' .request()->host().$baseDir.$fileDir.$fileName,
        );
    }

}