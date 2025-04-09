<?php

namespace app\admin\controller;
use app\admin\logic\AccountLogLogic;
use think\helper\Time;

class AccountLog extends AdminBase{
    /**
     * note 资金记录
     * create_time 2020/11/20 17:36
     */
    public function capitalList(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $list = AccountLogLogic::lists($get);
            $this->_success('',$list);
        }
        $this->assign('order_source',AccountLogLogic::orderSourceList(1));
        $this->assign('time',AccountLogLogic::getTime());
        return $this->fetch();
    }
    /**
     * note 积分记录
     * create_time 2020/11/20 17:36
     */
    public function integralList(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $list = AccountLogLogic::lists($get);
            $this->_success('',$list);
        }
        $this->assign('order_source',AccountLogLogic::orderSourceList(2));
        $this->assign('time',AccountLogLogic::getTime());
        return $this->fetch();
    }

    /**
     * note 成长值记录
     * create_time 2020/11/20 17:36
     */
    public function growthList(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $list = AccountLogLogic::lists($get);
            $this->_success('',$list);
        }
        $this->assign('order_source',AccountLogLogic::orderSourceList(3));
        $this->assign('time',AccountLogLogic::getTime());
        return $this->fetch();
    }



    /**
     * Notes: 佣金记录
     * @author 段誉(2021/5/15 11:36)
     * @return mixed
     */
    public function withdrawList()
    {
        if($this->request->isAjax()){
            $get = $this->request->get();
            $list = [];
            switch ($get['type']) {
                case 'distribution':
                    $list = AccountLogLogic::getDistributionLog($get);
                    break;
            }
            $this->_success('',$list);
        }
        return $this->fetch();
    }


    /**
     * Notes: 佣金统计
     * @author 段誉(2021/5/15 11:36)
     */
    public function withdrawTotalCount()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $result = AccountLogLogic::withdrawTotalCount($get);
            $this->_success('OK', $result);
        }
    }

}