<?php


namespace app\admin\controller;

use app\admin\logic\BabyLogic;

class Baby extends AdminBase
{

    /**
     * 宝宝列表
     * @return mixed
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('获取成功', BabyLogic::lists($get));
        }
        return $this->fetch();
    }

    /**
     * 宝宝身高历史列表
     * @return mixed
     */
    public function history_check()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('获取成功', BabyLogic::history_check($get));
        }
        return $this->fetch();
    }

    /**
     * 宝宝测评日志
     * @return mixed
     */
    public function history_log()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('获取成功', BabyLogic::history_check($get));
        }
        return $this->fetch();
    }

}