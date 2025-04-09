<?php


namespace app\admin\controller;


use app\admin\logic\SelffetchShopLogic;
use app\common\server\ConfigServer;

class SelffetchShop extends AdminBase
{
    /**
     * @notes 查看自提门店列表
     * @return mixed
     * @author ljj
     * @date 2021/8/14 6:02 下午
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $params = $this->request->get();
            $this->_success('', SelffetchShopLogic::lists($params));
        }

        return $this->fetch();
    }

    /**
     * @notes 添加自提门店
     * @return mixed
     * @author ljj
     * @date 2021/8/16 11:22 上午
     */
    public function add()
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $params = $this->request->post();
            $result = $this->validate($params, 'app\admin\validate\SelffetchShop.add');
            if ($result !== true) {
                $this->_error($result);
            }

            $result = SelffetchShopLogic::add($params);
            if ($result !== true) {
                $this->_error('添加失败:' . $result);
            }
            $this->_success('添加成功');
        }

        $this->assign('tx_map_key',ConfigServer::get('map','tx_map_key',''));
        $this->assign('area_lists', json_encode(SelffetchShopLogic::getAreaLists(), JSON_UNESCAPED_UNICODE));
        return $this->fetch();
    }

    /**
     * @notes 编辑自提门店
     * @param $id
     * @return mixed
     * @author ljj
     * @date 2021/8/16 2:15 下午
     */
    public function edit($id)
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $params = $this->request->post();
            $result = $this->validate($params, 'app\admin\validate\SelffetchShop.edit');
            if ($result !== true) {
                $this->_error($result);
            }

            $result = SelffetchShopLogic::edit($params);
            if ($result !== true) {
                $this->_error('修改失败:' . $result);
            }
            $this->_success('修改成功');
        }

        $this->assign('tx_map_key',ConfigServer::get('map','tx_map_key',''));
        $this->assign('area_lists', json_encode(SelffetchShopLogic::getAreaLists(), JSON_UNESCAPED_UNICODE));
        $this->assign('detail', SelffetchShopLogic::detail($id));
        return $this->fetch();
    }

    /**
     * @notes 修改自提门店状态
     * @author ljj
     * @date 2021/8/16 2:35 下午
     */
    public function status()
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $params = $this->request->post();
            $result = $this->validate($params, 'app\admin\validate\SelffetchShop.status');
            if ($result !== true) {
                $this->_error($result);
            }

            $result = SelffetchShopLogic::status($params);
            if ($result !== true) {
                $this->_error('修改失败:' . $result);
            }
            $this->_success('修改成功');
        }
    }

    /**
     * @notes 删除自提门店
     * @author ljj
     * @date 2021/8/16 2:42 下午
     */
    public function del()
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $params = $this->request->post();
            $result = $this->validate($params, 'app\admin\validate\SelffetchShop.del');
            if ($result !== true) {
                $this->_error($result);
            }

            $result = SelffetchShopLogic::del($params);
            if ($result !== true) {
                $this->_error('删除失败:' . $result);
            }
            $this->_success('删除成功');
        }
    }

    /**
     * @notes 导出列表
     * @author ljj
     * @date 2021/8/16 2:50 下午
     */
    public function exportFile()
    {
        $params = $this->request->get();
        $this->_success('', SelffetchShopLogic::exportFile($params));
    }
}