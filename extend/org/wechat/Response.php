<?php
namespace org\wechat;

class Response{
    public $data;
    public function __construct($data){
        $this->data = $data;
    }

    /**
     *
     * @param string $content
     * @return \think\Response
     */
    public function responseText($content=''){
        if(!$this->data){
            return content('success');
        }

        if(empty($content)){
            return content('success');
        }

        $content = trim(strip_tags($content));//去除html标签
        $now = time();
        return content( "<xml>
                    <ToUserName><![CDATA[{$this->data['FromUserName']}]]></ToUserName>
                    <FromUserName><![CDATA[{$this->data['ToUserName']}]]></FromUserName>
                    <CreateTime>{$now}</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[{$content}]]></Content>
                </xml>");
    }

    public function response2CustomerService(){
        if(!$this->data){
            return content('success');
        }

        $now = time();
        return content( "<xml>
                <ToUserName><![CDATA[{$this->data['FromUserName']}]]></ToUserName>
                <FromUserName><![CDATA[{$this->data['ToUserName']}]]></FromUserName>
                <CreateTime>{$now}</CreateTime>
                <MsgType><![CDATA[transfer_customer_service]]></MsgType>
            </xml>");
    }

    public function responseMedia($media_type,$media_id,$title=null,$description=null){
        switch ($media_type){
            case 'image':
                return $this->responseImg($media_id);
                break;
            case 'voice':
                return $this->responseVoice($media_id);
                break;
            case 'video':
                return  $this->responseVideo($media_id,$title,$description);
                break;
        }
        return content('success');
    }

    public function responseImg($media_id){
        if(!$this->data){
            return content('success');
        }

        $now = time();
        return content( "<xml>
                    <ToUserName><![CDATA[{$this->data['FromUserName']}]]></ToUserName>
                    <FromUserName><![CDATA[{$this->data['ToUserName']}]]></FromUserName>
                    <CreateTime>{$now}</CreateTime>
                    <MsgType><![CDATA[image]]></MsgType>
                    <Image><MediaId><![CDATA[{$media_id}]]></MediaId></Image>
                </xml>");
    }

    public function responseVoice($media_id){
        if(!$this->data){
            return content('success');
        }

        $now = time();
        return content(  "<xml>
                    <ToUserName><![CDATA[{$this->data['FromUserName']}]]></ToUserName>
                    <FromUserName><![CDATA[{$this->data['ToUserName']}]]></FromUserName>
                    <CreateTime>{$now}</CreateTime>
                    <MsgType><![CDATA[voice]]></MsgType>
                    <Voice><MediaId><![CDATA[{$media_id}]]></MediaId></Voice>
                </xml>");
    }

    public function responseVideo($media_id,$title='',$description=''){
        if(!$this->data){
            return content('success');
        }
        $title = trim(strip_tags($title));//去除html标签
        $description = trim(strip_tags($description));//去除html标签
        $now = time();
        return content(  "<xml>
                    <ToUserName><![CDATA[{$this->data['FromUserName']}]]></ToUserName>
                    <FromUserName><![CDATA[{$this->data['ToUserName']}]]></FromUserName>
                    <CreateTime>{$now}</CreateTime>
                    <MsgType><![CDATA[video]]></MsgType>
                    <Video>
                        <MediaId><![CDATA[{$media_id}]]></MediaId>
                        <Title><![CDATA[{$title}]]></Title>
                        <Description><![CDATA[{$description}]]></Description>
                    </Video>
                </xml>");
    }
}