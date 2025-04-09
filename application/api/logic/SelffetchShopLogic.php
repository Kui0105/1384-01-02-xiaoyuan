<?php


namespace app\api\logic;


use app\common\model\SelffetchShop;
use app\common\server\ConfigServer;

class SelffetchShopLogic
{
    /**
     * @notes 查看自提门店列表
     * @param $params
     * @param $page
     * @param $size
     * @return array
     * @author ljj
     * @date 2021/8/24 4:58 下午
     */
    public static function lists($params, $page, $size)
    {
        $longitude = $params['longitude'];
        $latitude = $params['latitude'];
        $distance = "round((6378.138*2*asin(sqrt(pow(sin(( {$latitude} *pi()/180-latitude*pi()/180)/2),2)+cos( {$latitude} *pi()/180)*cos(latitude*pi()/180)* pow(sin(( {$longitude} *pi()/180-longitude*pi()/180)/2),2)))*1000))/1000";

        $count = SelffetchShop::field(['id' => 'id', 'name' => 'name', $distance => 'distance','mobile' => 'mobile', 'business_start_time' => 'business_start_time','business_end_time' => 'business_end_time','province' => 'province','city' => 'city','district' => 'district','address' => 'address','longitude' => 'longitude','latitude' => 'latitude'])->where(['del'=>0,'status'=>1])->count();

        $lists = SelffetchShop::field(['id' => 'id', 'name' => 'name', $distance => 'distance','mobile' => 'mobile', 'business_start_time' => 'business_start_time','business_end_time' => 'business_end_time','province' => 'province','city' => 'city','district' => 'district','address' => 'address','longitude' => 'longitude','latitude' => 'latitude'])
            ->append(['shop_address'])
            ->hidden(['province','city','district','address'])
            ->where(['del'=>0,'status'=>1])
            ->order(['distance' => 'asc'])
            ->page($page, $size)
            ->select();

        foreach ($lists as &$item) {
            $item['distance'] = round($item['distance'], 2) . 'km';
            $item['business_status'] = 1;
//            if (strtotime($item['business_start_time']) <= time() && strtotime($item['business_end_time']) >= time()) {
//                $item['business_status'] = 1;
//            }
        }

        return [
            'list' => $lists,
            'count' => $count,
            'page' => $page,
            'size' => $size,
            'more' => is_more($count, $page, $size)
        ];
    }


    /**
     * @notes 获取地图密钥
     * @return array
     * @author 段誉
     * @date 2022/1/17 14:29
     */
    public static function getMapKey()
    {
        return [
            'tx_map_key' => ConfigServer::get('map','tx_map_key',''),
        ];
    }
}