<?php
namespace org\facade;
use think\Facade;


/**
 * 过滤器、事件触发器
 *
 * @see \org\XCHook
 * @mixin \org\XCHook
 * @method void clearFilter($tag) static 清除过滤器
 * @method void addAction($tag, $behavior,$priority=10) static 新增事件
 * @method void addFilter($tag, $behavior,$priority=10) static 新增过滤器
 * @method void doAction($tag,...$args) static 执行事件
 * @method array applyFilters($tag,...$args) static 执行过滤器
 */
class XCHook extends Facade
{
    protected static function getFacadeClass()
    {
        return 'org\XCHook';
    }
}