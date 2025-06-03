<?php

namespace app\api\controller;


use app\api\logic\CommunityLogic;

class Community extends ApiBase
{
    public $like_not_need_login = ['lists', 'detail', 'comment_lists'];

    /**
     * note 发帖
     */
    public function pub()
    {
        $post  = $this->request->post();
        $check = $this->validate($post, 'app\api\validate\Community.add');
        if (true !== $check) {
            $this->_error($check);
        }
        $res = CommunityLogic::add($post, $this->user_id);
        if ($res === true) {
            $this->_success('发布成功');
        }
        $this->_error($res);
    }

    /**
     * note 列表
     */
    public function lists()
    {
        $get      = $this->request->get();
        $com_list = CommunityLogic::ComList($this->user_id, $get, $this->page_no, $this->page_size);
        $this->_success('获取成功', $com_list);
    }

    /**
     * note 详情
     */
    public function detail()
    {
        $id = $this->request->get('id');
        if (!$id) {
            $this->_error('缺少参数');
        }
        $com = CommunityLogic::ComDetail($this->user_id, $id);
        if ($com) {
            $this->_success('获取成功', $com);
        }
        $this->_error(CommunityLogic::getError() ?? '失败');
    }

    /**
     * note 点赞
     */
    public function like()
    {
        $id = $this->request->get('id');
        if (!$id) {
            $this->_error('缺少参数');
        }
        $like = CommunityLogic::ComLike($this->user_id, $id);
        if ($like) {
            $this->_success($like);
        }
        $this->_error(CommunityLogic::getError() ?? '失败');
    }

    /**
     * note 评论
     */
    public function comment()
    {
        $post = $this->request->post();
        $res  = CommunityLogic::addComment($post, $this->user_id);
        if ($res === false) {
            $this->_error(CommunityLogic::getError() ?? '失败');
        }
        $this->_success('成功',$res);

    }

    /**
     * note 评论列表
     * 一级 和 二级
     * 带分页
     */
    public function comment_lists()
    {
        $id = $this->request->get('id');
        if (!$id) {
            $this->_error('缺少参数');
        }
        $comment_id = $this->request->get('comment_id', 0) ?: 0;
        $com_list   = CommunityLogic::CommentLists($this->user_id, $id, $comment_id, $this->page_no, $this->page_size);
        $this->_success('获取成功', $com_list);
    }

    /**
     * note 我的帖子列表
     */
    public function my_lists()
    {
        $get      = $this->request->get();
        $com_list = CommunityLogic::ComList($this->user_id, $get, $this->page_no, $this->page_size, true);
        $this->_success('获取成功', $com_list);
    }

    /**
     * note 我的帖子列表
     */
    public function del()
    {
        $id = $this->request->get('id');
        if (!$id) {
            $this->_error('缺少参数');
        }
        $del = CommunityLogic::ComDel($this->user_id, $id);
        if ($del === true) {
            $this->_success('已删除');
        }
        $this->_error($del);
    }
}