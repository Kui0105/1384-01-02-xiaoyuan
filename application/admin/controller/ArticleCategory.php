<?php

namespace app\admin\controller;
use app\admin\logic\{
    ArticleCategoryLogic
};
class ArticleCategory extends AdminBase {
    /**
     * 文章分类列表
     * @return mixed
     */
    public function lists(){
        $category_tree = ArticleCategoryLogic::categoryTwoTree();
        $this->assign('category_tree', json_encode($category_tree));
        return $this->fetch();
    }
    /**
     * 添加文章分类
     * @return mixed
     */
    public function add(){
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $post['type'] = 1;
            $result = $this->validate($post, 'app\admin\validate\ArticleCategory.add');
            if($result === true){
                ArticleCategoryLogic::addArticleCategory($post);
                $this->_success('添加成功！');
            }
            $this->_error($result);
        }
        $type = $this->request->get('type')??1;
        $category_list = ArticleCategoryLogic::getTopCategory($type);
        $this->assign('category_list', $category_list);
        return $this->fetch();
    }

    /**
     * 编辑文章分类
     * @return mixed
     */
    public function edit($id){
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $post['type'] = 1;
            $result = $this->validate($post, 'app\admin\validate\ArticleCategory.edit');
            if($result === true){
                ArticleCategoryLogic::editArticleCategory($post);
                $this->_success('编辑成功！');
            }
            $this->_error($result);
        }
        $category= ArticleCategoryLogic::getArticleCategory($id);
        $this->assign('category',array_values($category)[0]);
        $type = $this->request->get('type')??1;
        $category_list = ArticleCategoryLogic::getTopCategory($type);
        $this->assign('category_list', $category_list);
        return $this->fetch();
    }

    /**
     * 删除文章分类
     * @return mixed
     */
    public function del($id)
    {
        if ($this->request->isAjax()) {
            $result = $this->validate(['id' => $id], 'app\admin\validate\ArticleCategory.del');
            if ($result === true) {
                ArticleCategoryLogic::delArticleCategory($id);
                $this->_success('删除成功');
            }
            $this->_error($result);
        }
    }


    /**
     * 修改状态
     */
    public function switchStatus(){
        $post = $this->request->post();
        ArticleCategoryLogic::switchStatus($post);
        $this->_success('修改成功');
    }


    // ------------------------------------------------------------ 知识

    /**
     * 文章分类列表
     * @return mixed
     */
    public function lists1(){
        $category_tree = ArticleCategoryLogic::categoryTwoTree(2);
        $this->assign('category_tree', json_encode($category_tree));
        return $this->fetch();
    }

    /**
     * 添加文章分类
     * @return mixed
     */
    public function add1(){
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $post['type'] = 2;
            $result = $this->validate($post, 'app\admin\validate\ArticleCategory.add');
            if($result === true){
                ArticleCategoryLogic::addArticleCategory($post);
                $this->_success('添加成功！');
            }
            $this->_error($result);
        }
        return $this->fetch();
    }

    /**
     * 编辑文章分类
     * @return mixed
     */
    public function edit1($id){
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $post['type'] = 2;
            $result = $this->validate($post, 'app\admin\validate\ArticleCategory.edit');
            if($result === true){
                ArticleCategoryLogic::editArticleCategory($post);
                $this->_success('编辑成功！');
            }
            $this->_error($result);
        }
    }
}