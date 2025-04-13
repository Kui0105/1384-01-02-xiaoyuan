<?php

namespace app\admin\controller;
use app\admin\logic\ArticleCategoryLogic;
use app\admin\logic\GoodsCategoryLogic;
use app\admin\logic\CommonLogic;

class Common extends AdminBase{
    public function selectGoods(){
        if ($this->request->isAjax()){
            $get = $this->request->get();
            $goods_list = CommonLogic::getGoodsList($get,true);
            $this->_success('',$goods_list);
        }
        $category_list = GoodsCategoryLogic::categoryTreeeTree();
        $this->assign('category_list', $category_list);
        return $this->fetch();
    }
    public function selectGoodsCoupon(){
        if ($this->request->isAjax()){
            $get = $this->request->get();
            $goods_list = CommonLogic::getGoodsList($get,true);
            $this->_success('',$goods_list);
        }
        $category_list = GoodsCategoryLogic::categoryTreeeTree();
        $this->assign('category_list', $category_list);
        return $this->fetch();
    }

    //成长课堂 知识 选择器
    public function selectArticles()
    {
        if ($this->request->isAjax()){
            $get = $this->request->get();
            $goods_list = CommonLogic::getArticleList($get);
            $this->_success('',$goods_list);
        }
        $type = $this->request->get('type')??1;
        $this->assign('type', $type);
        $category = ArticleCategoryLogic::getTwoCategory($type);
        $this->assign('category_list', $category);
        return $this->fetch();
    }

    //时讯选择器
    public function selectIndexArticles()
    {
        if ($this->request->isAjax()){
            $get = $this->request->get();
            $goods_list = CommonLogic::getIndexArticleList($get);
            $this->_success('',$goods_list);
        }
        return $this->fetch();
    }

}