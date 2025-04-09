<?php

namespace app\api\controller;

use app\api\logic\MenuLogic;

class Menu extends ApiBase
{
    public $like_not_need_login = ['lists'];

    public function lists()
    {
        $type = $this->request->get('type', 1);
        $list = MenuLogic::getMenu($type,$this->user_info);
        return $this->_success('获取成功', $list);
    }
}