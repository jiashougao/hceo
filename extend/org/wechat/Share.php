<?php
namespace org\wechat;

use app\common\model\DomainHelper;
use org\helper\UrlHelper;
use think\Exception;

class Share
{
    /**
     * @var array
     */
    private $args;

    public function __construct($args) {
        $this->args = parse_args($args,array(
            'title'=>null,
            'desc'=>null,
            /**
             * 链接必须为本站站点下的链接
             */
            'url'=>null,

            /**
             * 图片地址必须为本站站点下的图片，(不能是云存储图片地址)
             * 1.用户头像本地地址获取方法：
             * User:getHeadImageUrlLocal($headImgUrl,$time);
             *  其中：$headImgUrl 云存储头像地址
             *       $time
             */
            'img'=>null,

        ));
    }

    public function script(){
        try{
            $token=$token = new Token(request()->current_expo);

            $ticket = $token->jsapiTicket();
            $url = UrlHelper::get_location_uri();
            $timestamp = time();
            $nonceStr = str_shuffle(time());
            $string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
            $signature = sha1($string);

            $signPackage = array(
                "appId"     => $token->appid,
                "nonceStr"  => "$nonceStr",
                "timestamp" => "$timestamp",
                "url"       => $url,
                "signature" => $signature,
                "rawString" => $string
            );
            ?><script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
            <script type="text/javascript">
                (function(){
                    wx.ready(function(){
                        let shareConfig = {
                            title: "<?php echo esc_attr($this->args['title'])?>", // 分享标题
                            desc: "<?php echo esc_attr($this->args['desc'])?>", // 分享描述
                            link: "<?php echo $this->args['url']?>", // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                            imgUrl: "<?php echo $this->args['img']?>", // 分享图标
                            success:function(){
                                $(document).trigger("handle_wechat_shared_succeed");
                            },
                            cancel:function() {

                            }
                        };

                        wx.updateAppMessageShareData(shareConfig);
                        //自定义“分享到朋友圈”及“分享到QQ空间”按钮的分享内容（1.4.0）
                        wx.updateTimelineShareData(shareConfig);
                        //获取“分享到朋友圈”按钮点击状态及自定义分享内容接口（即将废弃）
                        if(typeof wx.onMenuShareTimeline==="function"){
                            wx.onMenuShareTimeline(shareConfig);
                        }
                        //获取“分享给朋友”按钮点击状态及自定义分享内容接口（即将废弃）
                        if(typeof wx.onMenuShareAppMessage==="function"){
                            wx.onMenuShareAppMessage(shareConfig);
                        }
                        //获取“分享到QQ”按钮点击状态及自定义分享内容接口（即将废弃）
                        if(typeof wx.onMenuShareQQ==="function"){
                            wx.onMenuShareQQ(shareConfig);
                        }
                        //获取“分享到腾讯微博”按钮点击状态及自定义分享内容接口
                        if(typeof wx.onMenuShareWeibo==="function"){
                            wx.onMenuShareWeibo(shareConfig);
                        }
                        //获取“分享到QQ空间”按钮点击状态及自定义分享内容接口（即将废弃）
                        if(typeof wx.onMenuShareQZone==="function"){
                            wx.onMenuShareQZone(shareConfig);
                        }
                    });

                    let config = <?php echo json_encode($signPackage);?>;
                    config.debug = false;
                    config.jsApiList = [
                        "updateAppMessageShareData",
                        "updateTimelineShareData",
                        "onMenuShareTimeline",
                        "onMenuShareAppMessage",
                        "onMenuShareQQ",
                        "onMenuShareWeibo",
                        "onMenuShareQZone"
                    ];
                    wx.error(function(res){
                        alert('呃~，分享信息加载失败！请刷新页面！('+res.errMsg+')');
                    });

                    wx.config(config);
                })();
            </script>
            <?php
        }catch (\Exception $e){
            if(!request()->param('_r')){
                $url = UrlHelper::get_location_uri();
                $url .= (strpos($url,'?')===false?'?':'&').'_r=1';
                ?>
                <script type="text/javascript">
                    location.href="<?php echo esc_url($url)?>";
                </script>
                <?php
            }
        }
    }

    public function html($id){
        ?>
        <div style="display: none;" id="<?php echo esc_attr($id)?>" onclick="(function(){$('#<?php echo esc_attr($id)?>').hide();})();">
            <div class="dom" style="width:100%;height:100%;background-color:#000;position:fixed;top:0;left:0;z-index:99;opacity:0.8;filter: alpha(opacity=80);"></div>
            <div class="dom" style="position:absolute;top:0px;right:0px;z-index: 100">
                <img style="float:right;width: 80%;margin-right:10px;" src="/static/dist/img/login/share.png" />
            </div>
        </div>
        <?php
    }

    /**
     * @param $id
     * @throws Exception
     * @deprecated 3.0.0
     */
    public function form($id){

    }
}