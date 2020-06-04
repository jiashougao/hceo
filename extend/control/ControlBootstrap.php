<?php
namespace control;

/**
 * Bootstrap UI 下的表单渲染器
 *
 * 表单在Boostrap下的布局
 *
 * Class ControlBootstrap
 * @since v1.0.0
 * @author ranj
 * @package control
 */
class ControlBootstrap extends Control
{
    public function getFieldClass($type){
        $class = strtoupper($type[0]).substr($type,1);
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $class);

        return "\\control\\fields_bootstrap\\".$name;
    }
}