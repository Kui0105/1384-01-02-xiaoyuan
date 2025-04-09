<?php

namespace app\common\model;
use think\Model;

class Ad extends Model{
    const mobile = 1;
    const pc     = 2;


    public static function getAdTypeDesc($from = true){
        $desc = [
            self::mobile    => '移动端商城',
            self::pc        => 'pc端商城',
        ];
        if($from === true){
            return $desc;
        }
        return $desc[$from] ?? '';
    }

    public static function getLinkPage($type = true,$from = true){
        $page = [
            self::mobile    => [
                [
                    'name'      => '商品分类',
                    'path'      => '/pages/sort/sort',
                    'is_tab'    => 1,
                ],
                [
                    'name'      => '领券中心',
                    'path'      => '/pages/user_getcoupon/user_getcoupon',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '个人中心',
                    'path'      => '/pages/user/user',
                    'is_tab'    => 1,
                ],
                [
                    'name'      => '积分签到',
                    'path'      => '/bundle/pages/user_sign/user_sign',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '会员中心',
                    'path'      => '/pages/user_vip/user_vip',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '积分抽奖',
                    'path'      => '/bundle/pages/luckly_wheel/luckly_wheel',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '限时秒杀',
                    'path'      => '/bundle/pages/goods_seckill/goods_seckill',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '拼团活动',
                    'path'      => '/bundle/pages/goods_combination/goods_combination',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '砍价活动',
                    'path'      => '/bundle/pages/bargain/bargain',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '热销榜单',
                    'path'      => '/bundle/pages/hot_list/hot_list',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '商城资讯',
                    'path'      => '/pages/news_list/news_list',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '帮助中心',
                    'path'      => '/pages/news_list/news_list?type=1',
                    'is_tab'    => 0,
                ],
            ],
            self::pc        => [
                [
                    'name'      => '商品分类',
                    'path'      => '/category',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '领券中心',
                    'path'      => '/get_coupons',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '个人中心',
                    'path'      => '/user/profile',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '热销榜单',
                    'path'      => '/goods_list/1',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '新品推荐',
                    'path'      => '/goods_list/2',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '好物优选',
                    'path'      => '/goods_list/3',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '限时秒杀',
                    'path'      => '/seckill',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '商城资讯',
                    'path'      => '/news_list',
                    'is_tab'    => 0,
                ],
                [
                    'name'      => '帮助中心',
                    'path'      => '/help_center',
                    'is_tab'    => 0,
                ],
            ],
        ];
        if(true !== $type){
            $page = $page[$type] ?? [];
        }
        if(true === $from){
            return $page;
        }
        return $page[$from] ?? [];
    }


    public static function getGoodsPath($from = true){
        $desc = [
            self::mobile    => '/pages/goods_details/goods_details',
            self::pc        => '/goods_details',
        ];
        if(true === $from){
            return $desc;
        }
        return $desc[$from] ?? '';

    }


}