<?php


namespace app\common\model;


use think\Model;

class Verification extends Model
{
    //操作人类型
    const TYPE_SYSTEM   = 0;//系统
    const TYPE_ADMIN     = 1;//管理员
    const TYPE_USER     = 2;//会员

    //操作人类型
    public static function getOrderStatus($status = true)
    {
        $desc = [
            self::TYPE_SYSTEM => '系统',
            self::TYPE_ADMIN => '管理员',
            self::TYPE_USER => '会员',
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }
}