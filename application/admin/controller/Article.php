<?php


namespace app\admin\controller;

use app\admin\logic\{
    ArticleLogic,
    ArticleCategoryLogic
};

class Article extends AdminBase
{
    /**
     * 文章列表
     * @return mixed
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $get['type'] = 1;
            $this->_success('', ArticleLogic::lists($get));
        }
        $category = ArticleCategoryLogic::getTwoCategory(1);
        $this->assign('category_list', $category);
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
            $this->_success('', ArticleLogic::lists($get));
        }
        $category = ArticleCategoryLogic::getTwoCategory(2);
        $this->assign('category_list', $category);
        return $this->fetch();
    }

    /**
     * 添加文章
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $result = $this->validate($post, 'app\admin\validate\Article.add');
            if ($result === true) {
                ArticleLogic::addArticle($post);
                $this->_success('添加成功！');
            }
            $this->_error($result);

        }
        $type = $this->request->get('type')??1;
        $this->assign('type', $type);
        $acticle_category = ArticleCategoryLogic::getTwoCategory($type);
        $this->assign('category_list', $acticle_category);
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
            $result = $this->validate($post, 'app\admin\validate\Article.edit');

            if ($result === true) {
                ArticleLogic::editArticle($post);
                $this->_success('编辑成功！');
            }
            $this->_error($result);

        }
        $article = ArticleLogic::getArticle($id);
        $category_list = ArticleCategoryLogic::getTwoCategory($article['type']);
        $this->assign('article', $article);
        $this->assign('category_list', $category_list);
        return $this->fetch();
    }

    /**
     * 删除文章
     */
    public function del($id)
    {
        if ($this->request->isAjax()) {
            $result = $this->validate(['id' => $id], 'app\admin\validate\Article.del');
            if ($result === true) {
                ArticleLogic::delArticle($id);
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
        ArticleLogic::switchStatus($post);
        $this->_success('修改成功');
    }


}