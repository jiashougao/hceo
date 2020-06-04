<?php
namespace org\wechat\handler\event;

class SubscribeHandler extends BaseEventHandler
{

    function handle($request)
    {
        return $this->handleAuth($request);
    }
}