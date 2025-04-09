<?php


namespace app\api\controller;


use app\api\logic\FootPrintLogic;

class Footprint extends ApiBase
{
    public $like_not_need_login = ['lists'];

    public function lists()
    {
        $lists = FootPrintLogic::lists();
        $this->_success('获取成功', $lists);
    }
}