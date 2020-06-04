<?php
namespace org\wechat;
use app\common\model\OptionHelper;
use org\helper\FileHelper;
use org\helper\HttpHelper;
use org\helper\UrlHelper;
use org\qiniu\UploadHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\facade\Env;
use think\facade\Log;

class User{
    const DIR_WECHAT_HEADE = 'wechat/headv2/';
    const DIR_WECHAT_HEADE_PREG = 'wechat\/headv2\/';

    /**
     * @var Token
     */
    private $token;
    public function __construct($token){
       $this ->token = $token;
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
            request()->scheme() . '://' .DomainHelper::www().'.'.DomainHelper::domain().$baseDir.$fileDir.$cloudDir,
            $headImgUrl
        );
    }

    /**
     * 获取openid
     * @return bool|\think\response\Redirect
     * @throws Exception
     */
    public function getOpenid(){
        $code = request()->get('code');
        if (!$code){
            //触发微信返回code码
            $params = array();
            $params["appid"] = $this->token->appid;
            $params["redirect_uri"] =UrlHelper::get_location_uri();
            $params["response_type"] = "code";
            $params["scope"] = "snsapi_base";
            $params["state"] = str_shuffle(time());
            return redirect("https://open.weixin.qq.com/connect/oauth2/authorize?".http_build_query($params)."#wechat_redirect");
        }

        $params = array();
        $params["appid"] = $this->token->appid;
        $params["secret"] = $this->token->appsecret;
        $params["code"] = $_GET['code'];
        $params["grant_type"] = "authorization_code";

        $response = HttpHelper::GET( "https://api.weixin.qq.com/sns/oauth2/access_token?".http_build_query($params));
        if(!$response){
            throw new Exception('invalid callback data when get openid.');
        }

        //取出openid
        $data = maybe_json_decode($response,true);
        if(!is_array($data)){$data=array(
            'errcode'=>-1,
            'errmsg'=>'网络异常！'
        );}

        if(isset($data['errcode'])&&$data['errcode']!=0){
            throw new Exception($data['errmsg'],$data['errcode']);
        }

        return isset($data['openid'])?$data['openid']:false;
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
            'url_local'=>request()->scheme() . '://' .DomainHelper::www().'.'.DomainHelper::domain().$baseDir.$fileDir.$fileName,
        );
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function get(){
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $params = array();
            $params["appid"] = $this ->token->appid;
            $params["redirect_uri"] = UrlHelper::get_location_uri();
            $params["response_type"] = "code";
            $params["scope"] = "snsapi_userinfo";
            $params["state"] = time();

            return redirect("https://open.weixin.qq.com/connect/oauth2/authorize?".http_build_query($params)."#wechat_redirect");
        }

        $params = array();
        $params["appid"] = $this ->token->appid;
        $params["secret"] = $this ->token->appsecret;
        $params["code"] = $_GET['code'];
        $params["grant_type"] = "authorization_code";

        $response = HttpHelper::GET( "https://api.weixin.qq.com/sns/oauth2/access_token?".http_build_query($params));
        if(!$response){
            throw new Exception('invalid callback data when get openid.');
        }

        //取出openid
        $data = maybe_json_decode($response,true);
        if(!is_array($data)){$data=array(
            'errcode'=>-1,
            'errmsg'=>'网络异常！'
        );}
        if(isset($data['errcode'])&&$data['errcode']!=0){
            throw new Exception($data['errmsg'],$data['errcode']);
        }

        $openid =$data['openid'];
        $access_token = $data['access_token'];

        $result = HttpHelper::GET("https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid");
        $response = json_decode($result,true);
        if(!$response){
            throw new Exception('invalid callback data when get openid.');
        }
        if(!$response||(isset($response['errcode'])&&$response['errcode']!=0)){
            throw new Exception($response['errmsg'],$response['errcode']);
        }
        if(empty($response['unionid'])){
            $response['unionid'] ="u_{$response['openid']}";
        }
        return $response;
    }

    /**
     * 通过openid 获取用户信息
     * @param $openid
     * @param int $stop
     * @return mixed
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getByOpenid($openid,$stop = 0){
        try{
            $access_token = $this->token->accessToken();
            $response = HttpHelper::GET("https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN");
            $error = new Error($this ->token,$response);
            $response =  $error->get();
            if(empty($response['unionid'])){
                $response['unionid'] ="u_{$response['openid']}";
            }
            return $response;
        }catch (\Exception $e){
            if($e->getCode()===40001&&$stop<=2){
                return $this->getByOpenid($openid,$stop+1);
            }
            throw $e;
        }
    }
}