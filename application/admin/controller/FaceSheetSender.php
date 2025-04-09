<?php


namespace app\admin\controller;


use app\admin\logic\FaceSheetSenderLogic;

class FaceSheetSender extends AdminBase
{
    /**
     * @notes 发件人列表
     * @author 张无忌
     * @date 2021/9/26 14:26
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $list = FaceSheetSenderLogic::lists($get);
            $this->_success('获取成功', $list);
        }

        return $this->fetch();
    }

    /**
     * @notes 新增发件人模板
     * @author 张无忌
     * @date 2021/9/26 14:27
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = FaceSheetSenderLogic::add($post);
            if ($result !== true) {
                $this->_error($result);
            }
            $this->_success('新增成功');
        }

        return $this->fetch();
    }

    /**
     * @notes 编辑发件人
     * @author 张无忌
     * @date 2021/9/26 14:27
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = FaceSheetSenderLogic::edit($post);
            if ($result !== true) {
                $this->_error($result);
            }
            $this->_success('编辑成功');
        }

        $id = $this->request->get('id');
        $this->assign('detail', FaceSheetSenderLogic::detail($id));
        return $this->fetch();
    }

    /**
     * @notes 删除发件人
     * @author 张无忌
     * @date 2021/9/26 14:27
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            $result = FaceSheetSenderLogic::del($id);
            if ($result !== true) {
                $this->_error($result);
            }
            $this->_success('删除成功');
        }
    }
}