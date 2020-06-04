<?php
namespace org\wechat;
use app\common\model\SponsorMetaHelper;
use org\helper\HttpHelper;
use app\common\model\OptionHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;

class Token{
    protected $__temp=array();
    public $appid,$appsecret;

    /**
     * Token constructor.
     * @param array $expo 展届信息
     * @throws Exception
     */
    public function __construct($expo=null){
        if($expo){
            $helper = new SponsorMetaHelper($expo['sponsor_id']);
            $wechatConfig = $helper->get('wechat',array());
            if($wechatConfig&&isset($wechatConfig['wechat_reset'])&&$wechatConfig['wechat_reset']==='yes'){
                $this->appid = isset($wechatConfig['wechat_app_id'])?$wechatConfig['wechat_app_id']:'';
                $this->appsecret = isset($wechatConfig['wechat_app_secret'])?$wechatConfig['wechat_app_secret']:'';
                return;
            }
        }

        $this->appid  = OptionHelper::get('wechat_appid');
        $this->appsecret = OptionHelper::get('wechat_appsecret');
    }

    /**
     * @param $id
     * @param int $expire_at
     * @return mixed|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    private function get($id,&$expire_at=0){
        $token=null;
        if(isset($this->__temp[$id])){
            $token  = $this->__temp[$id];
        }else{
            $token = db_default('module_wechat_token')
                ->where(array(
                    'appid'=>$this->appid,
                    'token_key'=>$id
                ))
            ->find();
        }

        if(!$token||empty($token['token_value'])||$token['expire_at']<time()){
            return null;
        }
        
        $this->__temp[$id]=$token;
        $expire_at = $token['expire_at'];
        return $token['token_value'];
    }

    /**
     * @param $id
     * @param $data
     * @param $expired_in
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    private function set($id,$data,$expired_in){
        $tokenData=array(
            'token_key'=>$id,
            'appid'=>$this->appid,
            'expire_at'=>time()+$expired_in,
            'token_value'=>$data
        );

        $token = db_default('module_wechat_token')
            ->where(array(
                'appid'=>$this->appid,
                'token_key'=>$id
            ))
            ->find();

        if(!$token){
            $tokenData['id'] = db_default('module_wechat_token')
                                    ->insertGetId($tokenData);
        }else{
            db_default('module_wechat_token')
                ->where('id',$token['id'])
                ->update($tokenData);
            $tokenData['id'] = $token['id'];
        }

        $this->__temp[$id]=$tokenData;
        return $tokenData;
    }

    /**
     * @param bool $refresh
     * @param int $expire_at
     * @param int $stop
     * @return mixed|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function jsapiTicket($refresh=false,&$expire_at=0,$stop = 0){
        if(empty($this->appid)){
            throw new Exception('empty appid!');
        }

        if(empty($this->appsecret)){
            throw new Exception('empty appsecret!');
        }

        if(!$refresh){
            $ticket = $this->get('jsapi_ticket',$expire_at);
            if($ticket){
                return $ticket;
            }
        }

       try{
           $accessToken = $this->accessToken();
           $response = HttpHelper::GET( "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token={$accessToken}" );
           $error = new Error($this,$response);
           $obj = $error->get();

           $expire_at =absint($obj['expires_in']);
           $this->set('jsapi_ticket', $obj['ticket'],$expire_at);

           return $obj['ticket'];
       }catch (\Exception $e){
           if($e->getCode()===40001&&$stop<=2){
               return $this->jsapiTicket($refresh,$expire_at,$stop+1);
           }
           throw $e;
       }
    }

    /**
     * @param bool $refresh
     * @param int $expire_at
     * @return mixed|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public function accessToken($refresh=false,&$expire_at=0){
        if(empty($this->appid)){
            throw new Exception('empty appid!');
        }

        if(empty($this->appsecret)){
            throw new Exception('empty appsecret!');
        }
        
        if(!$refresh){
            $token = $this->get('access_token',$expire_at);
            if(!empty($token)){
                return $token;
            }
        }

        $response = HttpHelper::GET("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}");
        $error = new Error($this,$response);

        $obj = $error->get();
        $expire_at = absint($obj['expires_in']);
        $this->set('access_token', $obj['access_token'],$expire_at);

        return $obj['access_token'];
    }
}