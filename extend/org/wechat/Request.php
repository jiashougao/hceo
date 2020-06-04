<?php
namespace org\wechat;

use think\Exception;
use think\facade\Log;

class Request{
    /**
     * @var Token
     */
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @param $encoding_token
     * @param $encoding_aes_key
     * @return \think\Response
     */
    public function handle($encoding_token,$encoding_aes_key){
        $res = $this->validate($encoding_token);
        if($res instanceof \think\Response){
            return $res;
        }

        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        if (empty($xml)) {
            $xml = @file_get_contents("php://input");
        }

        if(empty($xml)){
            return content('invalid request!');
        }

        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$xml,true)){
            xml_parser_free($xml_parser);
            return content('invalid request!');
        }

        $hash = new Hash($this->token,$encoding_token,$encoding_aes_key);
        try{
            $request = $hash->decrypt($xml);
        }catch (\Exception $e){
            Log::error($e);
            return content($e->getMessage());
        }

        try{
            $handlerClass = "\\org\\wechat\\handler\\".strtoupper($request['MsgType'][0]).strtolower(substr($request['MsgType'],1))."Handler";
            if(class_exists($handlerClass)){
                $handler = new $handlerClass($this->token);
                $res = call_user_func_array(array($handler,'handle'),array($request));
                if($res && $res instanceof \think\Response){
                    return $res;
                }
            }
            return content('success');
        }catch (\Exception $e){
            Log::error($e);
            $response = new Response($request);
            return $response->responseText("系统内部异常！");
        }
    }

    private function validate($encoding_token){
        if(isset($_GET["echostr"])
            &&isset($_GET['signature'])
            &&isset($_GET['nonce'])
            &&isset($_GET['timestamp'])){
            if($this->checkSignature($encoding_token)){
                return content($_GET["echostr"]);
            }
        }
        return true;
    }

    private function checkSignature($encoding_token){
        //先获取到这三个参数
        $signature = $_GET['signature'];
        $nonce = $_GET['nonce'];
        $timestamp = $_GET['timestamp'];

        //把这三个参数存到一个数组里面
        $tmpArr = array($timestamp,$nonce,$encoding_token);
        //进行字典排序
        sort($tmpArr);

        //把数组中的元素合并成字符串，impode()函数是用来将一个数组合并成字符串的
        $tmpStr = implode($tmpArr);

        //sha1加密，调用sha1函数
        $tmpStr = sha1($tmpStr);
        //判断加密后的字符串是否和signature相等
        return $tmpStr == $signature;
    }
}