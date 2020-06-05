<?php
namespace app\services\user\model;
use think\model;
class SiteUser extends model
{
    protected $auto = ['created_time'];
    protected $insert = ['state' => 'active','status'=>'publish'];
    protected $update = ['last_updated_tim'];


    protected function setCreatedTimeAttr()
    {
        return time();
    }
    protected function setLastUpdatedTimAttr(){

        return time();
    }
}