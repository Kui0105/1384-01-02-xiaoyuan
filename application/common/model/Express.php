<?php


namespace app\common\model;


use think\Model;

class Express extends Model
{


    const ZHONGTONG  = 'zhongtong';
    const SHENTONG   = 'shentong';

    /**
     * @notes 获取快递100
     * @param bool $from
     * @return mixed|string
     * @author cjhao
     * @date 2022/9/29 10:46
     */
    public static function getkuaidi100code($from = true)
    {
        $desc = [
//            self::ZHONGTONG         => 'ztoOpen',
            self::SHENTONG          => '44',
        ];
        return $desc[$from] ?? '';
    }
}