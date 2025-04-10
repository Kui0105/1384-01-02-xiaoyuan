<?php


namespace app\admin\controller;

use app\admin\logic\{
    IndexNoticeLogic
};

class IndexNotice extends AdminBase
{
    /**
     * 帮助列表
     * @return mixed
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('', IndexNoticeLogic::lists($get));
        }
        return $this->fetch();
    }

    /**
     * 添加帮助
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = $this->validate($post, 'app\admin\validate\IndexNotice.add');
            if ($result === true) {
                IndexNoticeLogic::addNotice($post);
                $this->_success('添加成功！');
            }
            $this->_error($result);
        }
        return $this->fetch();
    }

    /**
     * 编辑帮助
     */
    public function edit($id)
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = $this->validate($post, 'app\admin\validate\IndexNotice.edit');
            if ($result === true) {
                IndexNoticeLogic::editNotice($post);
                $this->_success('编辑成功！');
            }
            $this->_error($result);
        }
        $notice = IndexNoticeLogic::getNotice($id);
        $this->assign('notice', $notice);
        return $this->fetch();
    }

    /**
     * 删除帮助
     */
    public function del($id)
    {
        if ($this->request->isAjax()) {
            $result = $this->validate(['id' => $id], 'app\admin\validate\IndexNotice.del');
            if ($result === true) {
                IndexNoticeLogic::delNotice($id);
                $this->_success('删除成功');
            }
            $this->_error($result);
        }
    }


    /**
     * 修改状态
     */
    public function switchStatus()
    {
        $post = $this->request->post();
        IndexNoticeLogic::switchStatus($post);
        $this->_success('修改成功');
    }


}