<?php


namespace app\admin\logic;

use app\admin\model\NutritionCategory;
use app\admin\model\NutritionFood;
use think\Db;

class NutritionFoodLogic
{

    public static function lists($get)
    {
        $where = [];
        $where[] = ['f.del', '=', '0'];

        $food = new NutritionFood();
        $count = $food->alias('f')->leftJoin('nutrition_category c','c.id = f.cid')->where($where)->count();
        $list = $food->alias('f')->leftJoin('nutrition_category c','c.id = f.cid')->where($where)
            ->field('f.*,c.name as cate_name')
            ->page($get['page'], $get['limit'])
            ->order('f.sort asc,f.id asc')
            ->select();

        return ['count' => $count, 'list' => $list];
    }

    /**
     * Desc: 添加食品
     * @param $post array 食品数据
     * @return boolean
     */
    public static function add($post)
    {
        if(!$post['cid']){
            return '请选择分类';
        }
        $food = new NutritionFood();
        $data = [
            'name'  => $post['name'],
            'cid'   => $post['cid'],
            'protein_lysine'   => abs($post['protein_lysine']),
            'vb'   => abs($post['vb']),
            'vd'   => abs($post['vd']),
            'iron' => abs($post['iron']),
            'zinc' => abs($post['zinc']),
            'va'   => abs($post['va']),
            'calcium'   => abs($post['calcium']),
            'dietary_fiber'   => abs($post['dietary_fiber']),
            'unhealthy'   => abs($post['unhealthy']),
            'sort'      => $post['sort'],
            'create_time' => time(),
        ];
        return $food->save($data);
    }

    /**
     * Desc: 编辑食品
     * @param $post array 食品数据
     * @return boolean
     */
    public static function edit($post)
    {
        if(!$post['cid']){
            return '请选择分类';
        }
        $food = new NutritionFood();
        $data = [
            'name'  => $post['name'],
            'cid'   => $post['cid'],
            'protein_lysine'   => abs($post['protein_lysine']),
            'vb'   => abs($post['vb']),
            'vd'   => abs($post['vd']),
            'iron' => abs($post['iron']),
            'zinc' => abs($post['zinc']),
            'va'   => abs($post['va']),
            'calcium'   => abs($post['calcium']),
            'dietary_fiber'   => abs($post['dietary_fiber']),
            'unhealthy'   => abs($post['unhealthy']),
            'sort'      => $post['sort'],
        ];
        return $food->save($data, ['id' => $post['id'], 'del' => 0]);
    }

    /**
     * Desc: 删除食品
     * @param $id int 食品id
     * @return boolean
     */
    public static function del($id)
    {
        $food = new NutritionFood();
        $data = [
            'del' => time(),
        ];
        return $food->save($data, ['id' => $id, 'del' => 0]);
    }

    /**
     * Desc: 获取单条食品
     * @param $id int 食品id
     * @return boolean
     */
    public static function getFood($id = 0)
    {
        $where[] = ['del', '=', 0];
        if ($id) {
            $where[] = ['id', '=', $id];
        }
        $detail =  Db::name('nutrition_food')->where($where)->find();
        if($detail){
            $detail['protein_lysine'] = $detail['protein_lysine'] > 0?floatval($detail['protein_lysine']):'';
            $detail['vb'] = $detail['vb'] > 0?floatval($detail['vb']):'';
            $detail['vd'] = $detail['vd'] > 0?floatval($detail['vd']):'';
            $detail['iron'] = $detail['iron'] > 0?floatval($detail['iron']):'';
            $detail['zinc'] = $detail['zinc'] > 0?floatval($detail['zinc']):'';
            $detail['va'] = $detail['va'] > 0?floatval($detail['va']):'';
            $detail['calcium'] = $detail['calcium'] > 0?floatval($detail['calcium']):'';
            $detail['dietary_fiber'] = $detail['dietary_fiber'] > 0?floatval($detail['dietary_fiber']):'';
            $detail['unhealthy'] = $detail['unhealthy'] > 0?floatval($detail['unhealthy']):'';
        }
        return $detail;
    }

    public static function switchStatus($post)
    {
        $data = [
            'is_show' => $post['is_show'],
            'update_time' => time(),
        ];
        return Db::name('nutrition_food')->where(['del' => 0, 'id' => $post['id']])->update($data);
    }
}
