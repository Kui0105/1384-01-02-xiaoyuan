<?php


namespace app\admin\logic;

use app\admin\model\NutritionCategory;
use app\admin\model\NutritionFood;
use think\Db;

class NutritionCategoryLogic
{

    public static function lists($get)
    {
        $where = [];
        $where[] = ['del', '=', '0'];

        $cate = new NutritionCategory();
        $count = $cate->where($where)->count();
        $list = $cate->where($where)
            ->page($get['page'], $get['limit'])
            ->order('sort asc,id desc')
            ->select();

        return ['count' => $count, 'list' => $list];
    }

    /**
     * Desc: 添加分类
     * @param $post array 分类数据
     * @return boolean
     */
    public static function add_cate($post)
    {
        if(!$post['img']){
            return '请上传图片';
        }
        $category = new NutritionCategory();
        $data = [
            'name' => $post['name'],
            'img' => clearDomain($post['img']),
            'sort' => $post['sort'],
            'create_time' => time(),
        ];
        return $category->save($data);
    }

    /**
     * Desc: 编辑分类
     * @param $post array 分类数据
     * @return boolean
     */
    public static function edit_cate($post)
    {
        if(!$post['img']){
            return '请上传图片';
        }
        $category = new NutritionCategory();
        $data = [
            'name' => $post['name'],
            'img' => clearDomain($post['img']),
            'sort' => $post['sort'],
        ];
        return $category->save($data, ['id' => $post['id'], 'del' => 0]);
    }

    /**
     * Desc: 删除分类
     * @param $id int 分类id
     * @return boolean
     */
    public static function delCate($id)
    {
        $son = NutritionFood::where('cid',$id)->where('del',0)->count();
        if($son){
            return '已关联食品';
        }
        $category = new NutritionCategory();
        $data = [
            'del' => time(),
        ];
        return $category->save($data, ['id' => $id, 'del' => 0]);
    }

    /**
     * Desc: 获取单条分类
     * @param $id int 分类id
     * @return boolean
     */
    public static function get_cate($id = 0)
    {
        $where[] = ['del', '=', 0];
        if ($id) {
            $where[] = ['id', '=', $id];
        }
        $category = new NutritionCategory();
        return $category->where($where)->find();
    }

    public static function getCategory()
    {
        $category = new NutritionCategory();
        return $category->where('del',0)->field('id,name')->order('sort asc, id desc')->select();
    }


    public static function switchStatus($post)
    {
        $data = [
            'is_show' => $post['is_show'],
            'update_time' => time(),
        ];
        return Db::name('nutrition_category')->where(['del' => 0, 'id' => $post['id']])->update($data);
    }
}
