<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/2/18
 * Time: 17:22
 */
namespace org\pcdesign;

/**
 * 网页编辑器，客户端类型声明
 * @since v1.0.0
 * @author ranj
 * Class Terminal
 * @package org\pcdesign
 */
class Terminal{
    const PC='pc';
    const PHONE = 'phone';

    public static function toArray(){
        return [
            self::PC=>[
                'label'=>'桌面端',
                'color'=>'primary'
            ],
            self::PHONE=>[
                'label'=>'移动端',
                'color'=>'success'
            ]
        ];
    }
}