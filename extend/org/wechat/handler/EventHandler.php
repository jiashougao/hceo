<?php

namespace org\wechat\handler;

class EventHandler extends BaseHandler
{

    function handle($request)
    {
        $eventClass = "org\\wechat\\handler\\event\\".strtoupper($request['Event'][0]).strtolower(substr($request['Event'],1))."Handler";
        if(class_exists($eventClass)){
            $handler = new $eventClass($this->token);
            $res = call_user_func_array(array($handler,'handle'),array($request));
            if($res&&$res instanceof \think\Response){
                return $res;
            }
        }
        return false;
    }
}