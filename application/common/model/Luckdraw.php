<?php


namespace app\common\model;


use app\admin\model\Coupon;
use think\Model;

class Luckdraw extends Model
{

    //计费方式
    const PRIZE_INTEGRAL = 1; //积分
    const PRIZE_NOT_WIN = 2; // 谢谢惠顾
    const PRIZE_COUPON = 3; //优惠券
    const PRIZE_BALANCE = 4;//按件计费


    public function getPrizeNameAttr($value, $data)
    {
        return self::getPrizeNameDesc($data['prize_type'], $data['number']);
    }


    public static function getPrizeNameDesc($type, $number)
    {
        switch ($type) {
            case self::PRIZE_INTEGRAL:
                $str = $number.'积分';
                break;
            case self::PRIZE_COUPON:
                $coupon = Coupon::where('id', $number)->value('name');
                $str = $coupon;
                break;
            case self::PRIZE_BALANCE:
                $str = $number.'元';
                break;
            default:
                $str = '谢谢惠顾';
        }
        return $str;
    }

    /**
     * @notes 获取奖品描述
     * @param $type
     * @return string|string[]
     * @author 段誉
     * @date 2022/2/15 10:24
     */
    public static function getPrizeDesc($type)
    {
        $data = [
            self::PRIZE_INTEGRAL => '积分',
            self::PRIZE_NOT_WIN => '谢谢惠顾',
            self::PRIZE_COUPON => '优惠券',
            self::PRIZE_BALANCE => '余额',
        ];

        if ($type === true) {
            return $data;
        }

        return $data[$type] ?? '';
    }

}