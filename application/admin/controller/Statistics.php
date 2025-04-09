<?php


namespace app\admin\controller;

use app\admin\logic\StatisticsLogic;

class Statistics extends AdminBase
{

    /**
     * @notes 获取会员统计数据
     * @return mixed
     * @author 段誉
     * @date 2022/4/27 10:51
     */
    public function member()
    {
        if ($this->request->isAjax()) {
            $params = $this->request->get();
            $this->_success('', StatisticsLogic::getMemberData($params));
        }
        $this->assign('time', StatisticsLogic::getDateData());
        return $this->fetch();
    }

    /**
     * @notes 获取商品统计数据
     * @return mixed
     * @author 段誉
     * @date 2022/4/27 15:04
     */
    public function goods()
    {
        if ($this->request->isAjax()) {
            $params = $this->request->get();
            $this->_success('', StatisticsLogic::getGoodsData($params));
        }
        $this->assign('time', StatisticsLogic::getDateData());
        return $this->fetch();
    }


    /**
     * @notes 获取交易数据
     * @return mixed
     * @author 段誉
     * @date 2022/4/27 15:33
     */
    public function deal()
    {
        if ($this->request->isAjax()) {
            $params = $this->request->get();
            $this->_success('', StatisticsLogic::getDealData($params));
        }
        $this->assign('time', StatisticsLogic::getDateData());
        return $this->fetch();
    }


    /**
     * @notes 获取访问数据
     * @return mixed
     * @author 段誉
     * @date 2022/4/27 15:47
     */
    public function visit()
    {
        if ($this->request->isAjax()) {
            $params = $this->request->get();
            $this->_success('', StatisticsLogic::getVisitData($params));
        }
        $this->assign('time', StatisticsLogic::getDateData());
        return $this->fetch();
    }
}