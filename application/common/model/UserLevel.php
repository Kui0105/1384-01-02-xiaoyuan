<?php


namespace app\common\model;


use think\Model;

class UserLevel extends Model
{
    /**
     * @notes 获取等级选项列表
     * @return array
     * @author lbzy
     * @datetime 2023-09-11 18:36:54
     */
    static function getSelectListDefault()
    {
        return static::where('del', 0)->field('id,name')->select()->toArray();
    }
    
    /**
     * @notes 获取等级选项列表
     * @return array
     * @author lbzy
     * @datetime 2023-09-11 18:36:54
     */
    static function getSelectList()
    {
        $result = [
            [
                'id'    => 0,
                'name'  => '无等级',
            ]
        ];
        
        $lists = static::where('del', 0)->field('id,name')->select()->toArray();
        
        foreach ($lists  as $level) {
            $result[] = $level;
        }
        
        return  $result;
    }
}