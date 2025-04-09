<?php

namespace app\admin\controller;

use app\admin\logic\FinanceLogic;



class Finance extends AdminBase
{
    public function lists()
    {
        $data = FinanceLogic::lists();
        $this->assign('data', $data);
        return $this->fetch();
    }
}