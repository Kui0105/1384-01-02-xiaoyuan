<?php

namespace app\admin\logic;

use app\common\model\HosOrder;
use app\common\model\User;
class HosOrderLogic{

    /**
     * @notes 当面付订单列表
     * @param $params
     * @return int[]
     * @author Tab
     */
    public static function lists($params)
    {
        $where = [];
        // 订单信息
        if (isset($params['order_keyword']) && !empty($params['order_keyword'])) {
            $where[] = ['o.order_sn|o.transaction_id', '=', $params['order_keyword']];
        }
        if (isset($params['hos_name']) && !empty($params['hos_name'])) {
            $where[] = ['o.hos_data->hos_name', '=', $params['hos_name']];
        }
        if (isset($params['pay_status']) && $params['pay_status'] !='') {
            $where[] = ['o.pay_status', '=', $params['pay_status']];
        }
        if (isset($params['user_keyword']) && !empty($params['user_keyword'])) {
            $where[] = ['u.sn|u.nickname|u.mobile', 'like', '%'.$params['user_keyword'].'%'];
        }

        $field = 'o.*,u.nickname,u.avatar,u.mobile';

        $lists = HosOrder::alias('o')
            ->leftJoin('user u', 'u.id = o.user_id')
            ->field($field)
            ->where($where)
            ->order('o.id', 'desc')
            ->page($params['page'], $params['limit'])
            ->select()
            ->toArray();

        $count = HosOrder::alias('o')
            ->leftJoin('user u', 'u.id = o.user_id')
            ->field($field)
            ->where($where)
            ->count();

        foreach($lists as &$item) {
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['pay_time'] = $item['pay_time']?date('Y-m-d H:i:s', $item['pay_time']):'';
            $item['avatar'] = addImgDomain($item['avatar']);
            $item['user_info'] = User::getUserInfo($item['hos_user_id']);
            $item['user_info']['avatar'] = empty($item['user_info']['avatar']) ? '' : addImgDomain($item['user_info']['avatar']);
            $item['hos_data'] = json_decode($item['hos_data'],true);
            $item['hos_data']['imgs'] = empty($item['hos_data']['imgs']) ? '' : addImgDomain($item['hos_data']['imgs']);
            $item['pay_status_str'] = $item['pay_status']?'已支付':'待支付';
            $item['transaction_id'] = $item['transaction_id']?:'';
        }

        return [
            'count' => $count,
            'lists' => $lists
        ];
    }
}