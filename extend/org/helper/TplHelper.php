<?php
namespace org\helper;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

/**
 * Class TplHelper
 * @since v1.0.0
 * @author ranj
 * @deprecated 已废弃
 * @package org\helper
 */
class TplHelper{
    private $expo;

    public function __construct($expo){
        $this->expo = $expo;
    }

    /**
     * 获取消息的模板配置
     * @param string $code 模板别名
     * @param string $type 模板类型 sms|wxtpl|email
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws \think\Exception
     */
    public function get($code,$type){
        $entity = db_default('module_expo_tpl')
            ->alias('et')
            ->join('module_tpl t','t.id = et.tpl_id')
            ->where(array(
                't.state'=>'active',
                'et.expo_id'=>$this->expo['id'],
                't.code'=>$code,
                't.type'=>$type
            ))
            ->field("t.code,t.title,et.content,t.type,t.created_time")
            ->find();
        if(!$entity){
            $entity = db_default('module_tpl')
                ->alias('t')
                ->where(array(
                    't.state'=>'active',
                    't.code'=>$code,
                    't.type'=>$type
                ))
                ->find();
        }
        return $entity;
    }
}