<?php
namespace org;
use think\Exception;
use think\Hook;
use think\Loader;
use think\Response;

/**
 *
 * 过滤器，事件触发器
 *
 * Class XCHook
 * @since v1.0.0
 * @author ranj
 * @package org
 */
class XCHook extends Hook{
    /**
     * 清除钩子函数
     * @param string $tag
     * @param string|array $behavior
     * @param bool $first
     */
    public function add($tag, $behavior, $first = false){
        parent::add($tag, $behavior, $first);
    }
    /**
     * 清除钩子函数
     * @param string $tag
     */
    public function clearFilter($tag){
        $this->add($tag,array(
            '_overlay'=>true
        ));
    }

    /**
     * 新增事件
     *
     * @param string $tag
     * @param string $behavior className
     * @param int $priority
     * @throws Exception
     */
    public function addAction($tag, $behavior,$priority=10){
        $this->addFilter($tag, $behavior,$priority);
    }

    /**
     * 新增过滤器
     *
     * @param string $tag
     * @param string $behavior className
     * @param int $priority
     * @throws Exception
     */
    public function addFilter($tag, $behavior,$priority=10){
        if(empty($tag)){
            throw new Exception("tag arg is required!");
        }

        $tags = $this->get($tag);
        $this->clearFilter($tag);

        $hasInsert = false;
        foreach ($tags as $index=>$item){
            if($index>=($priority+1)&&!$hasInsert){
                $this->add($tag,$behavior);
                $hasInsert = true;
            }
            $this->add($tag,$item);
        }

        if(!$hasInsert){
            $this->add($tag,$behavior);
        }
    }

    /**
     * 执行事件
     * @param $tag
     * @param array $args
     */
    public function doAction($tag,...$args){
        $tags  = $this->get($tag);
        foreach ($tags as $key => $class) {
            $call = null;
            if ($class instanceof \Closure) {
                $call  = $class;
            } elseif (is_array($class) || (is_string($class)&&strpos($class, '::'))) {
                $call = $class;
            } else if(is_string($class)&&function_exists($class)){
                $call  =$class;
            }
            if(!$call){
                continue;
            }
            $this->app->invoke($call, $args);
        }
    }

    /**
     * 执行过滤器
     * @param $tag
     * @param array $args
     * @return array|mixed
     * @throws Exception
     */
    public function applyFilters($tag,...$args){
        if(count($args)===0){
            throw new Exception("apply filters 必须至少包含一个参数");
        }

        $tags  = $this->get($tag);
        foreach ($tags as $key => $class) {
            $call = null;
            if ($class instanceof \Closure) {
                $call  = $class;
            } elseif (is_array($class) || (is_string($class)&&strpos($class, '::'))) {
                $call = $class;
            } else if(is_string($class)&&function_exists($class)){
                $call  =$class;
            }
            if(!$call){
                continue;
            }
            $args[0] = $this->app->invoke($call, $args);
            if($args[0] instanceof Response){
                return $args[0];
            }
        }

        return $args[0];
    }
}