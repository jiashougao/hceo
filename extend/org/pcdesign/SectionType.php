<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2020/2/17
 * Time: 12:07
 */
namespace org\pcdesign;

/**
 * 网页编辑器 通栏类型声明
 *
 * @since v1.0.0
 * @author ranj
 * Class SectionType
 * @package org\pcdesign
 */
class SectionType
{
    const SECTION ='sections';
    const HEADER ='header';
    const FOOTER ='footer';
    public static function toArray(){
        return [
            self::SECTION=>array(
                'label'=>'通栏',
                'color'=>'warning'
            ),
            self::HEADER=>array(
                'label'=>'导航',
                'color'=>'primary'
            ),
            self::FOOTER=>array(
                'label'=>'页脚',
                'color'=>'info'
            )
        ];
    }
}