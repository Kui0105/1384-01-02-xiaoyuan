<?php


namespace app\admin\controller;

use app\common\server\ConfigServer;
use app\admin\logic\{NutritionCategoryLogic,NutritionFoodLogic};

class Nutrition extends AdminBase
{
    /**
     * 帮助分类列表
     * @return mixed
     */
    public function category()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('', NutritionCategoryLogic::lists($get));
        }
        return $this->fetch();
    }

    /**
     * 添加帮助分类
     */
    public function add_cate()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $result = NutritionCategoryLogic::add_cate($post);
            if ($result === true) {
                $this->_success('添加成功！');
            }
            $this->_error($result);
        }
        return $this->fetch();
    }

    /**
     * 编辑帮助分类
     */
    public function edit_cate($id)
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = NutritionCategoryLogic::edit_cate($post);
            if ($result === true) {
                $this->_success('编辑成功！');
            }
            $this->_error($result);
        }

        $detail = NutritionCategoryLogic::get_cate($id);
        $this->assign('detail', $detail);
        return $this->fetch();
    }

    /**
     * 删除分类
     */
    public function del_cate($id)
    {
        if ($this->request->isAjax()) {
            $result = NutritionCategoryLogic::delCate($id);
            if ($result === true) {
                $this->_success('删除成功');
            }
            $this->_error($result);
        }
    }

    /**
     * 食品列表
     * @return mixed
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('', NutritionFoodLogic::lists($get));
        }
        return $this->fetch();
    }

    public function setting()
    {
        $config = [
            'protein_lysine_3_1'      => ConfigServer::get('nutrition', 'protein_lysine_3_1'),
            'protein_lysine_3_2'      => ConfigServer::get('nutrition', 'protein_lysine_3_2'),
            'protein_lysine_6_1'      => ConfigServer::get('nutrition', 'protein_lysine_6_1'),
            'protein_lysine_6_2'      => ConfigServer::get('nutrition', 'protein_lysine_6_2'),
            'protein_lysine_12_1'     => ConfigServer::get('nutrition', 'protein_lysine_12_1'),
            'protein_lysine_12_2'     => ConfigServer::get('nutrition', 'protein_lysine_12_2'),
            'protein_lysine_18_1'     => ConfigServer::get('nutrition', 'protein_lysine_18_1'),
            'protein_lysine_18_2'     => ConfigServer::get('nutrition', 'protein_lysine_18_2'),
            'vb_3_1'          => ConfigServer::get('nutrition', 'vb_3_1'),
            'vb_3_2'          => ConfigServer::get('nutrition', 'vb_3_2'),
            'vb_6_1'          => ConfigServer::get('nutrition', 'vb_6_1'),
            'vb_6_2'          => ConfigServer::get('nutrition', 'vb_6_2'),
            'vb_12_1'         => ConfigServer::get('nutrition', 'vb_12_1'),
            'vb_12_2'         => ConfigServer::get('nutrition', 'vb_12_2'),
            'vb_18_1'         => ConfigServer::get('nutrition', 'vb_18_1'),
            'vb_18_2'         => ConfigServer::get('nutrition', 'vb_18_2'),
            'vd_3_1'          => ConfigServer::get('nutrition', 'vd_3_1'),
            'vd_3_2'          => ConfigServer::get('nutrition', 'vd_3_2'),
            'vd_6_1'          => ConfigServer::get('nutrition', 'vd_6_1'),
            'vd_6_2'          => ConfigServer::get('nutrition', 'vd_6_2'),
            'vd_12_1'         => ConfigServer::get('nutrition', 'vd_12_1'),
            'vd_12_2'         => ConfigServer::get('nutrition', 'vd_12_2'),
            'vd_18_1'         => ConfigServer::get('nutrition', 'vd_18_1'),
            'vd_18_2'         => ConfigServer::get('nutrition', 'vd_18_2'),
            'iron_3_1'        => ConfigServer::get('nutrition', 'iron_3_1'),
            'iron_3_2'        => ConfigServer::get('nutrition', 'iron_3_2'),
            'iron_6_1'        => ConfigServer::get('nutrition', 'iron_6_1'),
            'iron_6_2'        => ConfigServer::get('nutrition', 'iron_6_2'),
            'iron_12_1'       => ConfigServer::get('nutrition', 'iron_12_1'),
            'iron_12_2'       => ConfigServer::get('nutrition', 'iron_12_2'),
            'iron_18_1'       => ConfigServer::get('nutrition', 'iron_18_1'),
            'iron_18_2'       => ConfigServer::get('nutrition', 'iron_18_2'),
            'zinc_3_1'        => ConfigServer::get('nutrition', 'zinc_3_1'),
            'zinc_3_2'        => ConfigServer::get('nutrition', 'zinc_3_2'),
            'zinc_6_1'        => ConfigServer::get('nutrition', 'zinc_6_1'),
            'zinc_6_2'        => ConfigServer::get('nutrition', 'zinc_6_2'),
            'zinc_12_1'       => ConfigServer::get('nutrition', 'zinc_12_1'),
            'zinc_12_2'       => ConfigServer::get('nutrition', 'zinc_12_2'),
            'zinc_18_1'       => ConfigServer::get('nutrition', 'zinc_18_1'),
            'zinc_18_2'       => ConfigServer::get('nutrition', 'zinc_18_2'),
            'va_3_1'          => ConfigServer::get('nutrition', 'va_3_1'),
            'va_3_2'          => ConfigServer::get('nutrition', 'va_3_2'),
            'va_6_1'          => ConfigServer::get('nutrition', 'va_6_1'),
            'va_6_2'          => ConfigServer::get('nutrition', 'va_6_2'),
            'va_12_1'         => ConfigServer::get('nutrition', 'va_12_1'),
            'va_12_2'         => ConfigServer::get('nutrition', 'va_12_2'),
            'va_18_1'         => ConfigServer::get('nutrition', 'va_18_1'),
            'va_18_2'         => ConfigServer::get('nutrition', 'va_18_2'),
            'calcium_3_1'     => ConfigServer::get('nutrition', 'calcium_3_1'),
            'calcium_3_2'     => ConfigServer::get('nutrition', 'calcium_3_2'),
            'calcium_6_1'     => ConfigServer::get('nutrition', 'calcium_6_1'),
            'calcium_6_2'     => ConfigServer::get('nutrition', 'calcium_6_2'),
            'calcium_12_1'    => ConfigServer::get('nutrition', 'calcium_12_1'),
            'calcium_12_2'    => ConfigServer::get('nutrition', 'calcium_12_2'),
            'calcium_18_1'    => ConfigServer::get('nutrition', 'calcium_18_1'),
            'calcium_18_2'    => ConfigServer::get('nutrition', 'calcium_18_2'),
            'dietary_fiber_3_1'       => ConfigServer::get('nutrition', 'dietary_fiber_3_1'),
            'dietary_fiber_3_2'       => ConfigServer::get('nutrition', 'dietary_fiber_3_2'),
            'dietary_fiber_6_1'       => ConfigServer::get('nutrition', 'dietary_fiber_6_1'),
            'dietary_fiber_6_2'       => ConfigServer::get('nutrition', 'dietary_fiber_6_2'),
            'dietary_fiber_12_1'      => ConfigServer::get('nutrition', 'dietary_fiber_12_1'),
            'dietary_fiber_12_2'      => ConfigServer::get('nutrition', 'dietary_fiber_12_2'),
            'dietary_fiber_18_1'      => ConfigServer::get('nutrition', 'dietary_fiber_18_1'),
            'dietary_fiber_18_2'      => ConfigServer::get('nutrition', 'dietary_fiber_18_2'),
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }

    public function setSetting()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('nutrition', 'protein_lysine_3_1', abFloatval($post['protein_lysine_3_1']));//蛋白质/赖氨酸
            ConfigServer::set('nutrition', 'protein_lysine_3_2', abFloatval($post['protein_lysine_3_2']));
            ConfigServer::set('nutrition', 'protein_lysine_6_1', abFloatval($post['protein_lysine_6_1']));
            ConfigServer::set('nutrition', 'protein_lysine_6_2', abFloatval($post['protein_lysine_6_2']));
            ConfigServer::set('nutrition', 'protein_lysine_12_1', abFloatval($post['protein_lysine_12_1']));
            ConfigServer::set('nutrition', 'protein_lysine_12_2', abFloatval($post['protein_lysine_12_2']));
            ConfigServer::set('nutrition', 'protein_lysine_18_1', abFloatval($post['protein_lysine_18_1']));
            ConfigServer::set('nutrition', 'protein_lysine_18_2', abFloatval($post['protein_lysine_18_2']));
            ConfigServer::set('nutrition', 'vb_3_1', abFloatval($post['vb_3_1']));//B族维生素
            ConfigServer::set('nutrition', 'vb_3_2', abFloatval($post['vb_3_2']));
            ConfigServer::set('nutrition', 'vb_6_1', abFloatval($post['vb_6_1']));
            ConfigServer::set('nutrition', 'vb_6_2', abFloatval($post['vb_6_2']));
            ConfigServer::set('nutrition', 'vb_12_1', abFloatval($post['vb_12_1']));
            ConfigServer::set('nutrition', 'vb_12_2', abFloatval($post['vb_12_2']));
            ConfigServer::set('nutrition', 'vb_18_1', abFloatval($post['vb_18_1']));
            ConfigServer::set('nutrition', 'vb_18_2', abFloatval($post['vb_18_2']));
            ConfigServer::set('nutrition', 'vd_3_1', abFloatval($post['vd_3_1']));//维生素D
            ConfigServer::set('nutrition', 'vd_3_2', abFloatval($post['vd_3_2']));
            ConfigServer::set('nutrition', 'vd_6_1', abFloatval($post['vd_6_1']));
            ConfigServer::set('nutrition', 'vd_6_2', abFloatval($post['vd_6_2']));
            ConfigServer::set('nutrition', 'vd_12_1', abFloatval($post['vd_12_1']));
            ConfigServer::set('nutrition', 'vd_12_2', abFloatval($post['vd_12_2']));
            ConfigServer::set('nutrition', 'vd_18_1', abFloatval($post['vd_18_1']));
            ConfigServer::set('nutrition', 'vd_18_2', abFloatval($post['vd_18_2']));
            ConfigServer::set('nutrition', 'iron_3_1', abFloatval($post['iron_3_1']));//铁
            ConfigServer::set('nutrition', 'iron_3_2', abFloatval($post['iron_3_2']));
            ConfigServer::set('nutrition', 'iron_6_1', abFloatval($post['iron_6_1']));
            ConfigServer::set('nutrition', 'iron_6_2', abFloatval($post['iron_6_2']));
            ConfigServer::set('nutrition', 'iron_12_1', abFloatval($post['iron_12_1']));
            ConfigServer::set('nutrition', 'iron_12_2', abFloatval($post['iron_12_2']));
            ConfigServer::set('nutrition', 'iron_18_1', abFloatval($post['iron_18_1']));
            ConfigServer::set('nutrition', 'iron_18_2', abFloatval($post['iron_18_2']));
            ConfigServer::set('nutrition', 'zinc_3_1', abFloatval($post['zinc_3_1']));//锌
            ConfigServer::set('nutrition', 'zinc_3_2', abFloatval($post['zinc_3_2']));
            ConfigServer::set('nutrition', 'zinc_6_1', abFloatval($post['zinc_6_1']));
            ConfigServer::set('nutrition', 'zinc_6_2', abFloatval($post['zinc_6_2']));
            ConfigServer::set('nutrition', 'zinc_12_1', abFloatval($post['zinc_12_1']));
            ConfigServer::set('nutrition', 'zinc_12_2', abFloatval($post['zinc_12_2']));
            ConfigServer::set('nutrition', 'zinc_18_1', abFloatval($post['zinc_18_1']));
            ConfigServer::set('nutrition', 'zinc_18_2', abFloatval($post['zinc_18_2']));
            ConfigServer::set('nutrition', 'va_3_1', abFloatval($post['va_3_1']));//维生素A
            ConfigServer::set('nutrition', 'va_3_2', abFloatval($post['va_3_2']));
            ConfigServer::set('nutrition', 'va_6_1', abFloatval($post['va_6_1']));
            ConfigServer::set('nutrition', 'va_6_2', abFloatval($post['va_6_2']));
            ConfigServer::set('nutrition', 'va_12_1', abFloatval($post['va_12_1']));
            ConfigServer::set('nutrition', 'va_12_2', abFloatval($post['va_12_2']));
            ConfigServer::set('nutrition', 'va_18_1', abFloatval($post['va_18_1']));
            ConfigServer::set('nutrition', 'va_18_2', abFloatval($post['va_18_2']));
            ConfigServer::set('nutrition', 'calcium_3_1', abFloatval($post['calcium_3_1']));//钙
            ConfigServer::set('nutrition', 'calcium_3_2', abFloatval($post['calcium_3_2']));
            ConfigServer::set('nutrition', 'calcium_6_1', abFloatval($post['calcium_6_1']));
            ConfigServer::set('nutrition', 'calcium_6_2', abFloatval($post['calcium_6_2']));
            ConfigServer::set('nutrition', 'calcium_12_1', abFloatval($post['calcium_12_1']));
            ConfigServer::set('nutrition', 'calcium_12_2', abFloatval($post['calcium_12_2']));
            ConfigServer::set('nutrition', 'calcium_18_1', abFloatval($post['calcium_18_1']));
            ConfigServer::set('nutrition', 'calcium_18_2', abFloatval($post['calcium_18_2']));
            ConfigServer::set('nutrition', 'dietary_fiber_3_1', abFloatval($post['dietary_fiber_3_1']));//膳食纤维
            ConfigServer::set('nutrition', 'dietary_fiber_3_2', abFloatval($post['dietary_fiber_3_2']));
            ConfigServer::set('nutrition', 'dietary_fiber_6_1', abFloatval($post['dietary_fiber_6_1']));
            ConfigServer::set('nutrition', 'dietary_fiber_6_2', abFloatval($post['dietary_fiber_6_2']));
            ConfigServer::set('nutrition', 'dietary_fiber_12_1', abFloatval($post['dietary_fiber_12_1']));
            ConfigServer::set('nutrition', 'dietary_fiber_12_2', abFloatval($post['dietary_fiber_12_2']));
            ConfigServer::set('nutrition', 'dietary_fiber_18_1', abFloatval($post['dietary_fiber_18_1']));
            ConfigServer::set('nutrition', 'dietary_fiber_18_2', abFloatval($post['dietary_fiber_18_2']));
            $this->_success('操作成功');
        }
    }

    public function points()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('', HelpCategoryLogic::lists($get));
        }
        return $this->fetch();
    }
    /**
     * 添加帮助分类
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = NutritionFoodLogic::add($post);
            if ($result === true) {
                $this->_success('添加成功！');
            }
            $this->_error($result);
        }
        $category = NutritionCategoryLogic::getCategory();
        $this->assign('category', $category);
        return $this->fetch();
    }

    /**
     * 编辑帮助分类
     */
    public function edit($id)
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = NutritionFoodLogic::edit($post);
            if ($result === true) {
                $this->_success('编辑成功！');
            }
            $this->_error($result);
        }
        $category = NutritionCategoryLogic::getCategory();
        $detail = NutritionFoodLogic::getFood($id);
        $this->assign('category', $category);
        $this->assign('detail', $detail);
        return $this->fetch();
    }

    /**
     * 删除帮助分类
     */
    public function del($id)
    {
        if ($this->request->isAjax()) {
            $result = NutritionFoodLogic::del($id);
            if ($result === true) {
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
        HelpCategoryLogic::switchStatus($post);
        $this->_success('修改成功');
    }

}