<?php


namespace app\admin\controller;


use app\admin\logic\FaceSheetOrderLogic;
use app\admin\logic\FaceSheetSenderLogic;
use app\admin\logic\FaceSheetLogic;

class FaceSheetOrder extends AdminBase
{
    /**
     * @notes 获取待发货订单列表
     * @return mixed
     * @author 张无忌
     * @date 2021/9/27 9:39
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = FaceSheetOrderLogic::lists($get);
            $this->_success('获取成功', $lists);
        }

        return $this->fetch();
    }

    /**
     * @notes 选择打印模板
     * @return mixed
     * @author 张无忌
     * @date 2021/9/27 15:59
     */
    public function select()
    {
        $this->assign('template', FaceSheetLogic::all());
        $this->assign('sender', FaceSheetSenderLogic::all());
        return $this->fetch();
    }

    /**
     * @notes 批量打印
     * @author 张无忌
     * @date 2021/9/27 14:49
     */
    public function print()
    {
        if ($this->request->isAjax()) {
            $order_id  = $this->request->post('order_id', 0, 'intval');
            $tempid    = $this->request->post('tempid', 0, 'intval');
            $sender_id = $this->request->post('sender_id', 0, 'intval');
            $result = FaceSheetOrderLogic::print($order_id, $tempid, $sender_id, $this->admin_id);
            if ($result !== true) {
                return $this->_error($result);
            }
            return $this->_success('打印成功');
        }

        return $this->_error('请求异常');
    }
}