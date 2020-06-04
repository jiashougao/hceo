<?php
namespace org\helper;
use think\Exception;


/**
 * 邮件发送
 *
 * Class EmailHelper
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class EmailHelper{
    //存储再session中的邮箱验证码的key
    const EMAIL_CODE_SESSION_KEY='email_code';

    /**
     * @var array
     */
    private $expo;
    private static $err_codes=array(
        0 => "提交成功",
        1 => "账号无效",
        2 => "密码错误",
        4 => "存在无效邮箱",
        5 => "Email超过最大个数",

        10 => "内容包含敏感词",
        11 => "余额不足",
        12 => "订购关系无效",

        14 => "无效的Email地址",
        16 => "邮址个数小于最小限制",
        17 => "邮件主题超过最大长度",

        97 => "接入方式错误",
        98 => "系统繁忙",
        99 => "消息格式错误"
    );

    public function __construct()
    {

    }

    /**
     * 统计邮箱IP发送记录
     *
     * @param $emails string|array 邮箱
     * @param int $interval
     * @throws Exception
     */
    public function validate($emails,$interval=60){
        if(!$emails){
            throw new Exception('invalid email');
        }

        if(is_string($emails)){
            $emails = array($emails);
        }

        $dataList = array();
        $now = strtotime(date('Y-m-d H:i:s'));
        foreach ($emails as $email){
            $entity = db_default('module_email')
                ->where("email",$email)
                ->where('created_time',">=",$now-$interval)
                ->find();
            if($entity){
                throw new Exception("Please wait {$interval} seconds and try again");
            }

            $dataList[]=array(
                'ip'=>get_client_ip(),
                'email'=>$email,
                'created_time'=>$now,
                'status'=>'prepare'
            );
        }
        db_default('module_email')
            ->insertAll($dataList);
    }

    /**
     * @param $emails string|array 邮箱
     * @param $subject
     * @param $tplCode string 模板别名
     * @param $params array  参数
     * @throws Exception
     */
    public function send($emails,$subject,$tplCode,$params=array())
    {
        $request = array(
            'email'    =>   $emails,
            'subject'   =>  $subject,
            'tplcode'    => $tplCode,
            'params'    =>  $params,
        );
        remote_request('api/tpl/sendemail',$request);

    }


}