<?php



namespace app\api\controller;


use app\api\logic\RegionLogic;

class Region extends ApiBase
{
    public $like_not_need_login = ['lists'];

    public function lists()
    {
        $lists = RegionLogic::lists();
        $this->_success('获取成功', $lists);
    }
}