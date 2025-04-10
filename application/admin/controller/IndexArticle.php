<?php


namespace app\admin\controller;

use app\admin\logic\{
    IndexArticleLogic
};

class IndexArticle extends AdminBase
{
    /**
     * 帮助列表
     * @return mixed
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('', IndexArticleLogic::lists($get));
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
            $result = $this->validate($post, 'app\admin\validate\IndexArticle.add');
            if ($result === true) {
                IndexArticleLogic::addArticle($post);
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
            $result = $this->validate($post, 'app\admin\validate\IndexArticle.edit');
            if ($result === true) {
                IndexArticleLogic::editArticle($post);
                $this->_success('编辑成功！');
            }
            $this->_error($result);
        }
        $article = IndexArticleLogic::getArticle($id);
        $this->assign('article', $article);
        return $this->fetch();
    }

    /**
     * 删除帮助
     */
    public function del($id)
    {
        if ($this->request->isAjax()) {
            $result = $this->validate(['id' => $id], 'app\admin\validate\IndexArticle.del');
            if ($result === true) {
                IndexArticleLogic::delArticle($id);
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
        IndexArticleLogic::switchStatus($post);
        $this->_success('修改成功');
    }


}