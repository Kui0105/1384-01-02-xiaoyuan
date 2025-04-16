<?php


namespace app\admin\controller;

use app\admin\logic\CommunityLogic;

class Community extends AdminBase
{
    /**
     * 文章列表
     * @return mixed
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('', CommunityLogic::lists($get));
        }
        return $this->fetch();
    }

    /**
     * 文章列表
     * @return mixed
     */
    public function lists1()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $get['type'] = 2;
            $this->_success('', CommunityLogic::lists($get));
        }
        return $this->fetch();
    }


    /**
     * 编辑文章
     */
    public function edit($id)
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $result = $this->validate($post, 'app\admin\validate\Community.edit');
            if ($result === true) {
                CommunityLogic::editArticle($post);
                $this->_success('编辑成功！');
            }
            $this->_error($result);
        }
        $article = CommunityLogic::getArticle($id);
        $this->assign('article', $article);
        return $this->fetch();
    }

    /**
     * 删除文章
     */
    public function del($id)
    {
        if ($this->request->isAjax()) {
            $result = $this->validate(['id' => $id], 'app\admin\validate\Community.del');
            if ($result === true) {
                CommunityLogic::delArticle($id);
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
        CommunityLogic::switchStatus($post);
        $this->_success('修改成功');
    }


    public function like_lists(){
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('', CommunityLogic::like_lists($get));
        }
        return $this->fetch();
    }

    public function comment_lists(){
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('', CommunityLogic::comment_lists($get));
        }
        return $this->fetch();
    }

    public function del_comment($id)
    {
        if ($this->request->isAjax()) {
            $result = $this->validate(['id' => $id], 'app\admin\validate\Community.del');
            if ($result === true) {
                CommunityLogic::delComment($id);
                $this->_success('删除成功');
            }
            $this->_error($result);
        }
    }

}