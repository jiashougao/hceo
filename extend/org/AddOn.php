<?php

namespace org;

/**
 * 系统插件
 *
 * @since v1.0.0
 * @author ranj
 * Class AddOn
 * @package org
 */
abstract class AddOn{
    public function __construct($options=array())
    {
        $this->extend = parse_args($options,$this->extend);
    }

    /**
     * 插件版本号
     * @var string
     */
    public $version = '1.0.0';

    /**
     * 核心库依赖
     * @var string
     */
    public $core_require_version ='>=1.0.0';

    /**
     * 第三方插件依赖
     * @var array
     */
    public $depends=[
//        'expo'=>'>=1.0.0',
//        'meeting'=>'<=2.0.1'
        //暂不支持 其它限制符号
        /**
        "plugin align" =>version:"1.0.0"   (大于)>=1.0.0 或 (小于)<=1.0.0或 (等于)1.0.0 | (任意)*
         */
    ];

    /**
     * 插件作者
     * @var string
     */
    public $author = '会展云';

    /**
     * 插件相关的网页地址
     * 包含插件介绍，帮组文档等
     * @var string
     */
    public $plugin_uri = 'http://www.messecloud.com';

    /**
     * 作者地址
     * @var string
     * @since 1.0.0
     */
    public $author_uri='http://www.messecloud.com';

    /**
     * 插件的扩展信息
     * 存储插件安装状态 等信息
     * @var
     */
    protected $extend=array();

    /**
     * 插件是否启用
     * @return bool
     */
    public function isActive(){
        return true;
    }

    /**
     * 获取插件模块的Module
     * @return string
     */
    public function getModule(){
        return isset($this->extend['align'])?$this->extend['align']:'';
    }
    /**
     * 执行插件更新
     * @param $oldVersion
     */
    public function onUpdate($oldVersion){}

    /**
     * 执行插件安装
     */
    public function onInstall(){}

    /**
     * 执行插件卸载
     */
    public function onUninstall(){}

    /**
     * 执行插件初始化
     */
    public function onInit(){}
}