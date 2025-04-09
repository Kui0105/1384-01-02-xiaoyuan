<?php


namespace app\admin\controller;


use app\admin\logic\SelffetchVerifierLogic;

class SelffetchVerifier extends AdminBase
{
    /**
     * @notes 核销员列表
     * @return mixed
     * @author ljj
     * @date 2021/8/16 3:30 下午
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $params = $this->request->get();
            $this->_success('', SelffetchVerifierLogic::lists($params));
        }

        return $this->fetch();
    }

    /**
     * @notes 添加核销员
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author ljj
     * @date 2021/8/16 4:45 下午
     */
    public function add()
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $params = $this->request->post();
            $result = $this->validate($params, 'app\admin\validate\SelffetchVerifier.add');
            if ($result !== true) {
                $this->_error($result);
            }

            $result = SelffetchVerifierLogic::add($params);
            if ($result !== true) {
                $this->_error('添加失败:' . $result);
            }
            $this->_success('添加成功');
        }

        $this->assign('shop_lists', SelffetchVerifierLogic::getShopLists());
        return $this->fetch();
    }

    /**
     * @notes 获取用户列表
     * @return mixed
     * @author ljj
     * @date 2021/8/19 2:42 下午
     */
    public function userLists()
    {
        if ($this->request->isAjax()) {
            $params = $this->request->get();
            $this->_success('', SelffetchVerifierLogic::getUserLists($params));
        }

        return $this->fetch();
    }

    /**
     * @notes 编辑核销员
     * @param $id
     * @return mixed
     * @author ljj
     * @date 2021/8/16 4:49 下午
     */
    public function edit($id)
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $params = $this->request->post();
            $result = $this->validate($params, 'app\admin\validate\SelffetchVerifier.edit');
            if ($result !== true) {
                $this->_error($result);
            }

            $result = SelffetchVerifierLogic::edit($params);
            if ($result !== true) {
                $this->_error('修改失败:' . $result);
            }
            $this->_success('修改成功');
        }

        //$this->assign('user_lists', SelffetchVerifierLogic::getUserLists());
        $this->assign('shop_lists', SelffetchVerifierLogic::getShopLists());
        $this->assign('detail', SelffetchVerifierLogic::detail($id));
        return $this->fetch();
    }

    /**
     * @notes 修改核销员状态
     * @author ljj
     * @date 2021/8/16 4:55 下午
     */
    public function status()
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $params = $this->request->post();
            $result = $this->validate($params, 'app\admin\validate\SelffetchVerifier.status');
            if ($result !== true) {
                $this->_error($result);
            }

            $result = SelffetchVerifierLogic::status($params);
            if ($result !== true) {
                $this->_error('修改失败:' . $result);
            }
            $this->_success('修改成功');
        }
    }

    /**
     * @notes 删除核销员
     * @author ljj
     * @date 2021/8/16 4:58 下午
     */
    public function del()
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $params = $this->request->post();
            $result = $this->validate($params, 'app\admin\validate\SelffetchVerifier.del');
            if ($result !== true) {
                $this->_error($result);
            }

            $result = SelffetchVerifierLogic::del($params);
            if ($result !== true) {
                $this->_error('删除失败:' . $result);
            }
            $this->_success('删除成功');
        }
    }

    /**
     * @notes 导出核销员列表
     * @author ljj
     * @date 2021/8/16 5:02 下午
     */
    public function exportFile()
    {
        $params = $this->request->get();
        $this->_success('', SelffetchVerifierLogic::exportFile($params));
    }
}