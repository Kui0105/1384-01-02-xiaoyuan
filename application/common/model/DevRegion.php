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
        $list =  self::field(['name', 'id', 'parent_id as pid', 'level'])->where('parent_id','in',$pid)->select()->toArray();
        Cache::set('area_pid_'.$pid, $list);
        return $list;
    }

    public static function getLevelSon($pid=100000,$level)
    {
        $cache = Cache::get('area_son_'.$level.'_'.$pid);
        if ($cache) {
            return $cache;
        }

        if($level == 2){
            $list =  self::field(['name', 'id', 'parent_id as pid', 'level'])->where('parent_id','in',$pid)->select()->toArray();
            foreach ($list as &$item){
                $item['child'] = self::getLevelArea($item['id']);
            }
        }else if($level == 3){
            $list =  self::field(['name', 'id', 'parent_id as pid', 'level'])->where('id','in',$pid)->select()->toArray();
            foreach ($list as &$item){
                $item['child'] = self::getLevelArea($item['id']);
            }
        }else{
            $pids = DevRegion::where('id','in',$pid)->column('parent_id');
            $list =  self::field(['name', 'id', 'parent_id as pid', 'level'])->where('id','in',$pids)->select()->toArray();
            foreach ($list as &$item){
                $item['child'] = DevRegion::field(['name', 'id', 'parent_id as pid', 'level'])->where('parent_id',$item['id'])->where('id','in',$pid)->select()->toArray();
            }

        }

        Cache::set('area_son_'.$level.'_'.$pid, $list);
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

    public static function getLevelAll()
    {
        $cache = Cache::get('area_level_all');
        if ($cache) {
            return $cache;
        }
        $list =  self::getLevelArea();
        foreach ($list as &$item){
            $son = self::getLevelArea($item['id']);

            foreach ($son as &$v){
                $v['child'] = self::getLevelArea($v['id']);
            }
            $item['child'] = $son;
        }
        Cache::set('area_level_all', $list);
        return $list;
    }

}