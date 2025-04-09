<?php


namespace app\common\model;


use think\Model;

class Goods extends Model
{
    const STATUS_RECYCLE = -1; // 回收站
    const STATUS_STORAGE = 0; // 下架(仓库中)
    const STATUS_SELL = 1; // 上架 (销售中)

    /**
     * @notes 获取商品状态
     * @param $state
     * @return string|string[]
     * @author 段誉
     * @date 2022/1/12 10:384
     */
    public static function getStatusDesc($state)
    {
        $data = [
            self::STATUS_SELL => '上架',
            self::STATUS_STORAGE => '下架',
            self::STATUS_RECYCLE => '回收站',
        ];
        if ($state === true) {
            return $data;
        }
        return $data[$state] ?? '';
    }

}