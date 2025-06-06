<?php

namespace app\api\logic;

use app\common\logic\LogicBase;
use app\common\server\ConfigServer;
use think\Db;

class NutritionLogic extends LogicBase
{
    public static function nutrition_ques()
    {
        $cate = Db::name('nutrition_category')->field('id,name,img')->where('del',0)->order('sort asc,id asc')->select();
        $food = Db::name('nutrition_food')->field('id,name,cid,0 as value')->where('del',0)->order('sort asc,id asc')->select();
        $food_list = [];
        foreach ($food as $f){
            $food_list[$f['cid']][] = $f;
        }
        foreach ($cate as $k => $c){
            if(empty($food_list[$c['id']])){
                unset($cate[$k]);
            }else{
                $cate[$k]['child'] = $food_list[$c['id']];
                $cate[$k]['img'] = addImgDomain($c['img']);
            }
        }
        $cate = array_merge($cate);
        return $cate;
    }
}