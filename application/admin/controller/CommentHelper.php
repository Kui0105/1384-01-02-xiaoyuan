<?php


namespace app\admin\controller;

use app\admin\logic\CommentHelperLogic;
use app\admin\logic\GoodsCategoryLogic;
use app\admin\logic\UserLogic;
use app\common\model\Goods;

/**
 * 评价助手
 * Class CommentHelper
 * @package app\admin\controller
 */
class CommentHelper extends AdminBase
{

    /**
     * @notes 商品列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 段誉
     * @date 2022/1/12 11:01
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('', CommentHelperLogic::lists($get));
        }
        $this->assign('category_list', GoodsCategoryLogic::categoryTreeeTree());
        $this->assign('status_list', Goods::getStatusDesc(true));
        return $this->fetch();
    }



    /**
     * @notes 添加虚拟评论
     * @return mixed
     * @author 段誉
     * @date 2022/1/12 11:00
     */
    public function add()
    {
        $goods_id = $this->request->get('id');
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $check = $this->validate($post, 'app\admin\validate\CommentHelper');
            if ($check !== true) {
                $this->_error($check);
            }
            $result = CommentHelperLogic::addComment($post);
            if (true === $result) {
                $this->_success('操作成功');
            }
            $this->_error($result);
        }
        $this->assign('user_level', UserLogic::getLevelList());
        $this->assign('goods_id', $goods_id);
        return $this->fetch();
    }

}