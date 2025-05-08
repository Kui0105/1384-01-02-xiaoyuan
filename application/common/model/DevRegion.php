<?php


namespace app\common\model;

use think\Db;
use think\facade\Cache;
use think\Model;

class DevRegion extends Model
{

    public static function getLevelArea($pid=100000)
    {
        $cache = Cache::get('area_pid_'.$pid);
        if ($cache) {
            return $cache;
        }
        $list =  self::field(['name', 'id', 'parent_id as pid', 'level'])->where('parent_id',$pid)->select()->toArray();
        Cache::set('area_pid_'.$pid, $list);
        return $list;
    }

    public static function getLevelSon($pid=100000,$level)
    {
        $cache = Cache::get('area_son_'.$pid);
        if ($cache) {
            return $cache;
        }
        $list =  self::field(['name', 'id', 'parent_id as pid', 'level'])->where('parent_id',$pid)->select()->toArray();
        if($level == 2){
            foreach ($list as &$item){
                $item['child'] = self::getLevelArea($item['id']);
            }
        }

        Cache::set('area_son_'.$pid, $list);
        return $list;
    }

    public static function getArea($id)
    {
        if(!$id){
            return '';
        }
        $cache = Cache::get('area_id_'.$id);
        if ($cache) {
            return $cache;
        }
        $name =  self::where('id',$id)->value('name');
        Cache::set('area_id_'.$id, $name);
        return $name;
    }

    public static function getMoreArea($ids)
    {
        if(!$ids){
            return '';
        }
        $cache_name = str_replace(',', '_', $ids);
        $cache = Cache::get('area_ids_'.$cache_name);
        if ($cache) {
            return $cache;
        }
        $name_arr =  self::where('id','in',$ids)->column('name');
        $str = implode(',',$name_arr);
        Cache::set('area_ids_'.$cache_name, $str);
        return $str;
    }


}