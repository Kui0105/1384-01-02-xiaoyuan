<?php

namespace app\common\logic;

use app\common\model\DevRegion;
use app\common\model\DistributionGoods;
use app\common\model\DistributionLevel;
use app\common\model\DistributionOrderGoods;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\User;
use app\common\server\ConfigServer;
use think\Db;

class DistributionOrderGoodsLogic extends LogicBase
{
    /**
     * @notes 生成分销订单
     * @param $order_id
     * @param $user_id
     * @author Tab
     */
    public static function add($orderId)
    {
        // 获取分销配置
        // $distributionConfig = [
        //     'is_open' => ConfigServer::get('distribution', 'is_open', 0),
        //     'level' => ConfigServer::get('distribution', 'level', 2),
        // ];
        // if(!$distributionConfig['is_open']) {
        //     return false;
        // }

        // 用户信息
        $order = Order::where('id', $orderId)->findOrEmpty()->toArray();
        // $userInfo = self::userInfo($order['user_id']);

        // 订单信息
        $orderInfo = self::orderInfo($orderId);
        // 遍历子订单
        foreach($orderInfo as $item) {
            // 判断商品是否参与分销
            $goodsDistribution = self::checkGoodsDistribution($item['goods_id']);

            if(empty($goodsDistribution) || !$goodsDistribution['is_distribution']) {
                // 商品未参与分销
                continue;
            }

            self::NewLevelCommission($item);

            // // 分销层级
            // switch($distributionConfig['level'])
            // {
            //     case 1: // 一级分销
            //         self::firstLevelCommission($userInfo, $item, $goodsDistribution);
            //         break;
            //     case 2: // 一级、二级分销
            //         self::firstLevelCommission($userInfo, $item, $goodsDistribution);
            //         self::secondLevelCommission($userInfo, $item, $goodsDistribution);
            //         break;
            // }
        }

    }

    /**
     * @notes 根据商品实际支付金额计算佣金
     * @param $item
     * @param $userInfo
     * @return float
     * @author Tab
     */
    public static function calByPaymentAmountNew($total_pay_price, $radio)
    {
        $earnings = round(($total_pay_price * $radio / 100), 2);
        return $earnings;
    }

    public static function NewLevelCommission($item)
    {
        $insert_data = $hos_data = $area_data = $city_data = $province_data = [];
        //诊所
        $earnings = 0;
        $radio_data = Db::name('user_new_level_radio')->where(['level'=>4,'self_id'=>$item['hos_id']])->find();
        if(!$radio_data){
            return false;
        }
        $name = Db::name('user_new_level')->where('id',$item['hos_id'])->value('name');
        if(!$name){
            $name = Db::name('user')->where('id',$radio_data['user_id'])->value('nickname');
        }
        $earnings = self::calByPaymentAmountNew($item['total_pay_price'], $radio_data['radio']);
        if($earnings > 0) {
            $hos_data = [
                'sn' => createSn('distribution_order_goods', 'sn'),
                'user_id' => $radio_data['user_id'],
                'real_name' => $name,
                'level_id' => 4,
                'level' => 1,
                'ratio' => $radio_data['radio'],
                'order_id' => $item['order_id'],
                'order_goods_id' => $item['order_goods_id'],
                'goods_num' => $item['goods_num'],
                'money' => $earnings,
                'status' => 1,
                'create_time' => time(),
            ];
        }

        //区代
        $area_id = $radio_data['pid'];
        $city_id = 0;
        $earnings = 0;
        $radio_data = Db::name('user_new_level_radio')->where(['level'=>3,'self_id'=>$area_id])->find();
        if($radio_data){
            $city_id = $radio_data['pid'];
            $name = Db::name('user_new_level')->where('level',3)->where("find_in_set({$area_id},self_id)")->value('name');
            if(!$name){
                $name = Db::name('user')->where('id',$radio_data['user_id'])->value('nickname');
            }
            $earnings = self::calByPaymentAmountNew($item['total_pay_price'], $radio_data['radio']);
            if($earnings > 0) {
                $area_data = [
                    'sn' => createSn('distribution_order_goods', 'sn'),
                    'user_id' => $radio_data['user_id'],
                    'real_name' => $name,
                    'level_id' => 3,
                    'level' => 2,
                    'ratio' => $radio_data['radio'],
                    'order_id' => $item['order_id'],
                    'order_goods_id' => $item['order_goods_id'],
                    'goods_num' => $item['goods_num'],
                    'money' => $earnings,
                    'status' => 1,
                    'create_time' => time(),
                ];
            }
        }else{
            $area_radio = ConfigServer::get('distribution', 'area_point', 0);
            $earnings = self::calByPaymentAmountNew($item['total_pay_price'], $area_radio);
            if($earnings > 0) {
                $area_data = [
                    'sn' => createSn('distribution_order_goods', 'sn'),
                    'user_id' => 0,
                    'real_name' => '',
                    'level_id' => 3,
                    'level' => 2,
                    'ratio' => $area_radio,
                    'order_id' => $item['order_id'],
                    'order_goods_id' => $item['order_goods_id'],
                    'goods_num' => $item['goods_num'],
                    'money' => $earnings,
                    'status' => 1,
                    'create_time' => time(),
                ];
            }
        }

        //市代
        $city_id = $city_id?:DevRegion::where('id',$area_id)->value('parent_id');
        $province_id = 0;
        $earnings = 0;
        $radio_data = Db::name('user_new_level_radio')->where(['level'=>2,'self_id'=>$city_id])->find();
        if($radio_data){
            $province_id = $radio_data['pid'];
            $name = Db::name('user_new_level')->where('level',2)->where("find_in_set({$city_id},self_id)")->value('name');
            if(!$name){
                $name = Db::name('user')->where('id',$radio_data['user_id'])->value('nickname');
            }
            $earnings = self::calByPaymentAmountNew($item['total_pay_price'], $radio_data['radio']);
            if($earnings > 0) {
                $city_data = [
                    'sn' => createSn('distribution_order_goods', 'sn'),
                    'user_id' => $radio_data['user_id'],
                    'real_name' => $name,
                    'level_id' => 2,
                    'level' => 3,
                    'ratio' => $radio_data['radio'],
                    'order_id' => $item['order_id'],
                    'order_goods_id' => $item['order_goods_id'],
                    'goods_num' => $item['goods_num'],
                    'money' => $earnings,
                    'status' => 1,
                    'create_time' => time(),
                ];
            }
            //区代没人
            if(!empty($area_data)){
                if(!$area_data['user_id']){
                    $area_data['user_id'] = $radio_data['user_id'];
                    $area_data['real_name'] = $name;
                }
            }
        }else{
            $city_radio = ConfigServer::get('distribution', 'city_point', 0);
            $earnings = self::calByPaymentAmountNew($item['total_pay_price'], $city_radio);
            if($earnings > 0) {
                $city_data = [
                    'sn' => createSn('distribution_order_goods', 'sn'),
                    'user_id' => 0,
                    'real_name' => '',
                    'level_id' => 2,
                    'level' => 3,
                    'ratio' => $city_radio,
                    'order_id' => $item['order_id'],
                    'order_goods_id' => $item['order_goods_id'],
                    'goods_num' => $item['goods_num'],
                    'money' => $earnings,
                    'status' => 1,
                    'create_time' => time(),
                ];
            }
        }

        //省代
        $province_id = $province_id?:DevRegion::where('id',$city_id)->value('parent_id');
        $earnings = 0;
        $radio_data = Db::name('user_new_level_radio')->where(['level'=>1,'self_id'=>$province_id])->find();
        if($radio_data){
            $name = Db::name('user_new_level')->where('level',1)->where("find_in_set({$province_id},self_id)")->value('name');
            if(!$name){
                $name = Db::name('user')->where('id',$radio_data['user_id'])->value('nickname');
            }
            $earnings = self::calByPaymentAmountNew($item['total_pay_price'], $radio_data['radio']);
            if($earnings > 0) {
                $province_data = [
                    'sn' => createSn('distribution_order_goods', 'sn'),
                    'user_id' => $radio_data['user_id'],
                    'real_name' => $name,
                    'level_id' => 1,
                    'level' => 4,
                    'ratio' => $radio_data['radio'],
                    'order_id' => $item['order_id'],
                    'order_goods_id' => $item['order_goods_id'],
                    'goods_num' => $item['goods_num'],
                    'money' => $earnings,
                    'status' => 1,
                    'create_time' => time(),
                ];
            }
            //区代没人
            if(!empty($area_data)){
                if(!$area_data['user_id']){
                    $area_data['user_id'] = $radio_data['user_id'];
                    $area_data['real_name'] = $name;
                }
            }
            //市代没人
            if(!empty($city_data)){
                if(!$city_data['user_id']){
                    $city_data['user_id'] = $radio_data['user_id'];
                    $city_data['real_name'] = $name;
                }
            }
        }


        if(!empty($hos_data) && $hos_data['user_id']){
            $insert_data[] = $hos_data;
        }
        if(!empty($area_data) && $area_data['user_id']){
            $insert_data[] = $area_data;
        }
        if(!empty($city_data) && $city_data['user_id']){
            $insert_data[] = $city_data;
        }
        if(!empty($province_data) && $province_data['user_id']){
            $insert_data[] = $province_data;
        }
        if(!empty($insert_data)){
            DistributionOrderGoods::insertAll($insert_data);
        }

    }
    /**
     * @notes 用户信息
     * @param $userId
     * @return mixed
     * @author Tab
     */
    public static function userInfo($userId)
    {
        $userInfo = User::alias('u')
            ->leftJoin('distribution d', 'd.user_id = u.id')
            ->field('u.id,u.nickname,u.first_leader,u.second_leader,d.level_id,d.is_distribution,d.is_freeze')
            ->where('u.id', $userId)
            ->findOrEmpty()
            ->toArray();

        return $userInfo;
    }

    /**
     * @notes 订单信息
     * @param $orderId
     * @return mixed
     * @author Tab
     */
    public static function orderInfo($orderId)
    {
        $orderInfo = OrderGoods::alias('og')
            ->leftJoin('order o', 'o.id = og.order_id')
            ->field('og.id as order_goods_id,og.order_id,og.goods_id,og.item_id,og.goods_num,og.total_pay_price,o.user_id,o.hos_id')
            ->where('og.order_id', $orderId)
            ->select()
            ->toArray();
        return $orderInfo;
    }

    /**
     * @notes 校验商品是否参与分销
     * @param $goodsId
     * @return array
     * @author Tab
     */
    public static function checkGoodsDistribution($goodsId)
    {
        $distributionGoods = DistributionGoods::field('goods_id,item_id,level_id,first_ratio,second_ratio,is_distribution,rule')
            ->where('goods_id', $goodsId)
            ->select()
            ->toArray();

        if(empty($distributionGoods)) {
            return [];
        }

        return [
            'goods_id' => $distributionGoods[0]['goods_id'],
            'is_distribution' => $distributionGoods[0]['is_distribution'],
            'rule' => $distributionGoods[0]['rule']
        ];
    }

    /**
     * @notes 一级分佣
     * @param $userInfo
     * @param $item
     * @param $goodsDistribution
     * @return false
     * @author Tab
     */
    public static function firstLevelCommission($userInfo, $item, $goodsDistribution)
    {
        if(!$userInfo['first_leader']) {
            // 没有上级，无需分佣
            return false;
        }
        $firstLeaderInfo = self::userInfo($userInfo['first_leader']);
        if(!$firstLeaderInfo['is_distribution'] || $firstLeaderInfo['is_freeze']) {
            // 上级不是分销会员 或 分销资格已冻结
            return false;
        }

        $ratioArr = self::getRatio($goodsDistribution, $item, $firstLeaderInfo);
        $firstLeaderInfo['ratio'] = $ratioArr['first_ratio'];
        $firstLeaderInfo['level'] = 1;
        self::addDistributionOrderGoods($item, $firstLeaderInfo);
    }

    /**
     * @notes 获取分佣比例
     * @param $goodsDistribution
     * @param $item
     * @param $userInfo
     * @return array
     * @author Tab
     */
    public static function getRatio($goodsDistribution, $item, $userInfo)
    {
        // 按分销会员等级对应的比例
        if($goodsDistribution['rule'] == 1) {
            $ratioArr = DistributionLevel::field('first_ratio,second_ratio')
                ->where('id', $userInfo['level_id'])
                ->findOrEmpty()
                ->toArray();
            return $ratioArr;
        }

        // 单独设置的比例
        if($goodsDistribution['rule'] == 2) {
            $ratioArr = DistributionGoods::field('first_ratio,second_ratio')
                ->where([
                    'goods_id' => $item['goods_id'],
                    'item_id' => $item['item_id'],
                    'level_id' => $userInfo['level_id']
                ])
                ->findOrEmpty()
                ->toArray();
            return $ratioArr;
        }
    }

    /**
     * @notes 二级分佣
     * @param $userInfo
     * @param $item
     * @param $goodsDistribution
     * @return false
     * @author Tab
     */
    public static function secondLevelCommission($userInfo, $item, $goodsDistribution)
    {
        if(!$userInfo['second_leader']) {
            // 没有上上级，无需分佣
            return false;
        }
        $secondLeaderInfo = self::userInfo($userInfo['second_leader']);
        if(!$secondLeaderInfo['is_distribution'] || $secondLeaderInfo['is_freeze']) {
            // 上上级不是分销会员 或 分销资格已冻结
            return false;
        }

        $ratioArr = self::getRatio($goodsDistribution, $item, $secondLeaderInfo);
        $secondLeaderInfo['ratio'] = $ratioArr['second_ratio'];
        $secondLeaderInfo['level'] = 2;
        self::addDistributionOrderGoods($item, $secondLeaderInfo);
    }

    /**
     * @notes 生成分销订单
     * @param $item
     * @param $userInfo
     * @return false
     * @author Tab
     */
    public static function addDistributionOrderGoods($item, $userInfo)
    {
        $earnings = 0;
        $earnings = self::calByPaymentAmount($item, $userInfo);
        if($earnings < 0.01) {
            return false;
        }
        $data = [
            'sn' => createSn('distribution_order_goods', 'sn'),
            'user_id' => $userInfo['id'],
            'real_name' => $userInfo['nickname'],
            'level_id' => $userInfo['level_id'],
            'level' => $userInfo['level'],
            'ratio' => $userInfo['ratio'],
            'order_id' => $item['order_id'],
            'order_goods_id' => $item['order_goods_id'],
            'goods_num' => $item['goods_num'],
            'money' => $earnings,
            'status' => 1,
            'create_time' => time(),
        ];

        DistributionOrderGoods::create($data);
    }

    /**
     * @notes 根据商品实际支付金额计算佣金
     * @param $item
     * @param $userInfo
     * @return float
     * @author Tab
     */
    public static function calByPaymentAmount($item, $userInfo)
    {
        $earnings = round(($item['total_pay_price'] * $userInfo['ratio'] / 100), 2);
        return $earnings;
    }
}