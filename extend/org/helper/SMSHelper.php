<?php
namespace org\helper;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;

/**
 * 短信发送工具类
 *
 * Class SMSHelper
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class SMSHelper{

    //存储再session中的短信验证码的key
    const SMS_CODE_SESSION_KEY='sms_code';

    /**
     * @var array
     */
    private $sponsor;
    private static $err_codes=array(
        0 => "提交成功",
        1 => "账号无效",
        2 => "密码错误",
        3 => "msgid不唯一",
        4 => "存在无效手机号码",
        5 => "手机号码个数超过最大限制",
        6 => "短信内容超过最大限制",
        7 => "扩展子号码无效",
        8 => "发送时间格式无效",
        9 => "请求来源地址无效",
        10 => "内容包含敏感词",
        11 => "余额不足",
        12 => "订购关系无效",
        13 => "短信签名无效",
        14 => "无效的手机号码",
        15 => "产品不存在",
        16 => "号码个数小于最小限制",
        17 => "同一号码类似内容提交过快",
        18 => "每秒请求次数应在20以内",
        19 => "产品不存在",
        20 => "同一号码提交过快",
        24 => "签名未报备，无法提交",
        25 => "查询msgId超过最大个数",
        26 => "存在黑名单",
        27 => "此员工没开通短信功能！",
        28 => "员工月限额不足！",
        29 => "签名黑名单",
        88 => "接入方式错误",
        89 => "批量发送短信过多",
        90 => "SID错误",
        93 => "IP错误",
        94 => "账户已禁用",
        95 => "账户未审核",
        96 => "账户未激活",
        97 => "接入方式错误",
        98 => "系统繁忙",
        99 => "消息格式错误"
    );

    public function __construct()
    {

    }

    /**
     * 统计手机IP发送记录
     *
     * @param $mobiles string|array 手机号
     * @param int $interval
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function validate($mobiles,$interval=60){
        if(is_string($mobiles)){
            if(!$mobiles){
                throw new Exception('invalid phone number');
            }

            $mobiles = array($mobiles);
        }

        $dataList = array();
        $now = strtotime(date('Y-m-d H:i:s'));
        foreach ($mobiles as $mobile){
            $entity = db_default('module_sms')
                ->where("mobile",$mobile)
                ->where('created_time',">=",$now-$interval)
                ->find();
            if($entity){
                throw new Exception("请等待{$interval}秒后重试！");
            }

            $dataList[]=array(
                'ip'=>get_client_ip(),
                'mobile'=>$mobile,
                'created_time'=>$now,
                'status'=>'prepare'
            );
        }
        db_default('module_sms')
            ->insertAll($dataList);
    }

    /**
     * 记录发送成功
     *
     * @param $mobiles
     * @throws Exception
     * @throws PDOException
     */
    public function handleSuccess($mobiles){
        if(is_string($mobiles)){
            if(!$mobiles){
                throw new Exception('invalid phone number');
            }

            $mobiles = array($mobiles);
        }

        foreach ($mobiles as $mobile){
            db_default('module_sms')
                ->where("mobile",$mobile)
                ->where('ip',get_client_ip())
                ->update(array(
                   'status'=> 'success'
                ));
        }
    }



    /**
     * @param $mobiles string|array 手机号
     * @param $tplCode string 模板别名
     * @param $params array  参数
     * @throws Exception
     */
    public function send($mobiles,$tplCode,$params=array())
    {
        $request = array(
            'mobile'    =>  $mobiles,
            'tplcode'   =>  $tplCode,
            'params'    =>  $params
        );
        remote_request('api/tpl/sendsms',$request);
    }

}