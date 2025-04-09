<?php


namespace app\common\model;

use think\Model;

/**
 * 分销订单
 * Class DistributionOrder
 * @package app\common\model
 */
class DistributionOrder extends Model
{
    protected $name = 'distribution_order_goods';

    //分销订单状态
    const STATUS_WAIT_HANDLE = 1;//待返佣
    const STATUS_SUCCESS = 2;//已结算
    const STATUS_ERROR = 3;//已失效


    //分销订单状态
    public static function getOrderStatus($status = true)
    {
        $desc = [
            self::STATUS_WAIT_HANDLE => '待返佣',
            self::STATUS_SUCCESS => '已结算',
            self::STATUS_ERROR => '已失效',
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }


    /**
     * Notes: 更新指定分佣订单状态
     * @param $distribution_id
     * @param $status
     * @author 段誉(2021/4/23 10:10)
     * @return DistributionOrder
     */
    public static function updateOrderStatus($distribution_id, $status)
    {
        $time = time();
        return self::where('id', $distribution_id)
            ->update([
                'status' => $status,
                'settlement_time' => $time,
                'update_time' => $time
            ]);
    }


    //用户
    public function user()
    {
        return $this->hasOne('user', 'id', 'user_id')
            ->field('id,sn,nickname,avatar,level,mobile,sex,create_time');
    }

}