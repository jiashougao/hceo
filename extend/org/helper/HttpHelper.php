<?php
namespace org\helper;
use think\Exception;
use think\facade\Env;

/**
 * HTTP GET POST请求
 *
 * Class HttpHelper
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class HttpHelper
{
    /**
     * @param $url
     * @param bool $ssl
     * @param int $timeout
     * @return bool|string
     * @throws Exception
     */
    public static function GET($url, $ssl = false,$timeout=60)
    {
        $ch = curl_init();
        return self::request($ch, $url, $ssl,$timeout);
    }

    /**
     * @param $url
     * @param array $data
     * @param bool $ssl
     * @param int $timeout
     * @return bool|string
     * @throws Exception
     */
    public static function POST($url, $data = array(), $ssl = false,$timeout=60)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, TRUE);

        if (is_array($data)) {
            $data = http_build_query($data);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        return self::request($ch, $url, $ssl,$timeout);
    }

    /**
     * @param $ch
     * @param $url
     * @param bool $ssl
     * @param int $timeout
     * @return bool|string
     * @throws Exception
     */
    public static function request($ch, $url, $ssl = false,$timeout=60)
    {
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO,  Env::get('root_path').'certificates/ca-bundle.crt');
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($httpStatusCode != 200) {
            throw new Exception("status:{$httpStatusCode},response:$response,error:" . $error, $httpStatusCode);
        }
        return $response;
    }

    /**
     * @param $url
     * @param $dir
     * @param int $timeout
     * @return bool|string
     * @throws Exception
     */
//    public static function curl_download($url, $dir,$timeout=60)
//    {
//        $ch = curl_init($url);
//        $fp = fopen($dir, "wb");
//        curl_setopt($ch,CURLOPT_POST,0);
//        curl_setopt($ch, CURLOPT_FILE, $fp);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        //连接超时
//        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$timeout);
//        //curl最大执行时间
//        curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
//        $res=curl_exec($ch);
//        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        $error = curl_error($ch);
//        curl_close($ch);
//        fclose($fp);
//        if ($httpStatusCode != 200) {
//            throw new Exception("status:{$httpStatusCode},response:$res,error:" . $error, $httpStatusCode);
//        }
//        return $res;
//    }


    /**
     * 异步请求查询访问分布
     * @param  $url     源地址
     * @param  $save_to 保存路径
     * @return 返回写入到文件内数据的字节数，失败时返回FALSE
     */
    public static function curl_down($url,$save_to,$timeout=60)
    {
        $ch             =   curl_init();
        curl_setopt($ch,CURLOPT_POST,0);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        //连接超时
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$timeout);
        //curl最大执行时间
        curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
        $file_content   =   curl_exec($ch);
        //方式一：
        return file_put_contents($save_to, $file_content);
        //方式二：
        /*$downloaded_file=fopen($save_to,'w');
        fwrite($downloaded_file,$file_content);
        fclose($downloaded_file);*/

    }
}