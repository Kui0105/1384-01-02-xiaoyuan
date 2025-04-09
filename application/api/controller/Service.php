<?php

namespace app\api\controller;
use app\admin\logic\ServiceConfigLogic;

class Service extends ApiBase
{
    public $like_not_need_login = ['lists'];
    public function lists()
    {
        $list = ServiceConfigLogic::getConfig()['config'];
        $this->_success('获取成功',$list);
    }
}