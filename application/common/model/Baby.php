<?php

namespace app\common\model;

use think\Model;

class Baby extends Model
{

    public static function get_type_str($type)
    {
        $arr = [
            1 => '身高测评',
            2 => '脾胃测评',
            3 => '睡眠测评',
            4 => '骨龄测评',
            5 => '运动测评',
            6 => '营养测评'
        ];
        return $arr[$type];
    }
}