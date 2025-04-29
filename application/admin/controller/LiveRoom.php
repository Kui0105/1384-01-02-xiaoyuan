<?php


namespace app\admin\controller;


use app\admin\logic\LiveRoomLogic;

class LiveRoom extends AdminBase
{
    public $like_not_need_login = ['upload'];

    /**
     * @notes 获取直播间列表
     * @return mixed|void
     * @author 张无忌
     * @date 2021/9/13 15:57
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = LiveRoomLogic::lists($get);
            // if (is_string($lists)) {
            //     return $this->_error($lists);
            // }
            $this->_success('获取成功', $lists);
        }

        return $this->fetch();
    }

    /**
     * @notes 创建直播间
     * @return mixed
     * @author 张无忌
     * @date 2021/9/13 15:57
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = LiveRoomLogic::add($post);
            if ($result !== true) {
                return $this->_error($result);
            }
            $this->_success('创建成功');
        }

        return $this->fetch();
    }

    /**
     * 编辑
     */
    public function edit($id)
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            LiveRoomLogic::editLive($post);
            $this->_success('编辑成功！');
        }
        $live = LiveRoomLogic::getLive($id);
        $this->assign('live', $live);
        return $this->fetch();
    }

    /**
     * @notes 删除直播间
     * @return bool|void
     * @author 张无忌
     * @date 2021/9/14 18:50
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            $result = LiveRoomLogic::del($id);
            if ($result !== true) {
                return $this->_error($result);
            }
            $this->_success('删除成功');
        }

        return false;
    }

    /**
     * @notes 上传临时图片
     * @return mixed|void
     * @author 张无忌
     * @date 2021/9/14 14:51
     */
    public function uploadImage()
    {
        if ($this->request->isPost()) {
            $file = request()->file('file');
            $info = $file->move( './uploads/temp');
            $result = LiveRoomLogic::upload($info->getSaveName());
            if (is_string($result)) {
                return $this->_error($result);
            }
            return $this->_success('上传成功', $result);
        }

        return $this->fetch();
    }

}