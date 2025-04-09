<?php



namespace app\api\logic;


use think\Db;
use think\facade\Cache;

class RegionLogic
{
    public static function lists()
    {
        $cache = Cache::get('dev_region_tree');
        if ($cache) {
            return $cache;
        } else {
            $lists = Db::name('dev_region')
                ->field('id as value,parent_id as pid,name as label')
                ->select();
            $lists =  linear_to_tree($lists, 'children', 'value');
            Cache::set('dev_region_tree', $lists);
            return $lists;
        }
    }
}