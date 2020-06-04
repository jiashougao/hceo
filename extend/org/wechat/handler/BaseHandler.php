<?php
namespace org\wechat\handler;

use org\wechat\Token;
use think\Response;

abstract class BaseHandler
{
    /**
     * @var Token
     */
    protected $token;

    /**
     * BaseHandler constructor.
     * @param Token $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @param $request
     * @return Response|false
     */
    public function handle($request){
        return false;
    }
}