<?php
namespace org\wechat\handler\event;

class ScanHandler extends BaseEventHandler
{

    function handle($request)
    {
        return $this->handleAuth($request);
    }
}