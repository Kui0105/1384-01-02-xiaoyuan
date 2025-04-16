<?php

namespace app\common\model;
use think\Model;

class AccountLog extends Model{
    /*******************************
     ** 余额变动：100~199
     ** 小元币变动：200~299
     ** 成长值变动：300~399
     ** 佣金变动: 400~499
     *******************************/
    const admin_add_money       = 100;
    const admin_reduce_money    = 101;
    const recharge_money        = 102;
    const balance_pay_order     = 103;
    const cancel_order_refund   = 104;
    const after_sale_refund     = 105;
    const withdraw_to_balance   = 106;
    const user_transfer_inc_balance = 107;
    const user_transfer_dec_balance = 108;
    const luck_draw_inc_balance     = 109;

    const admin_add_integral           = 200;
    const admin_reduce_integral        = 201;
    const sign_in_integral             = 202;
    const recharge_give_integral       = 203;
    const order_add_integral           = 204;
    const register_add_integral        = 205;
    const invite_add_integral          = 206;
    const order_deduction_integral     = 207;
    const cancel_order_refund_integral = 208;
    const luck_draw_integral           = 209;
    const deduct_order_first_integral  = 210;
    const order_goods_give_integral    = 211;
    const luck_draw_dec_integral       = 212;

    const check_height                 = 220;
    const check_weight                 = 221;
    const order_send                   = 222;
    const invite_user                  = 223;
    const community_like               = 224;
    const community_comment            = 225;
    const share_article                = 226;
    const watch_video                  = 227;

    const admin_add_growth      = 300;
    const admin_reduce_growth   = 301;
    const sign_give_growth      = 302;
    const recharge_give_growth  = 303;
    const order_give_growth     = 304;//下单赠送成长值

    const withdraw_dec_earnings     = 400;//提现扣减佣金
    const withdraw_back_earnings    = 401;//提现被拒绝返回佣金
    const distribution_inc_earnings = 402;//分销订单结算增加佣金
    const admin_inc_earnings = 403; //后台增加佣金
    const admin_reduce_earnings = 404; //后台减少佣金

    const money_change = [      //余额变动类型
        self::admin_add_money,self::admin_reduce_money,self::recharge_money,self::balance_pay_order,self::cancel_order_refund,self::after_sale_refund
        , self::withdraw_to_balance,self::user_transfer_inc_balance, self::user_transfer_dec_balance, self::luck_draw_inc_balance
    ];
    const integral_change = [   //小元币变动类型
        self::admin_add_integral,self::admin_reduce_integral,self::sign_in_integral,self::recharge_give_integral,self::order_add_integral,self::invite_add_integral
        , self::order_deduction_integral,self::register_add_integral,self::cancel_order_refund_integral,self::luck_draw_integral,self::deduct_order_first_integral
        , self::order_goods_give_integral, self::luck_draw_dec_integral
        ,self::check_height,self::check_weight,self::order_send,self::invite_user,self::community_like,self::community_comment,self::share_article,self::watch_video
    ];
    const growth_change = [     //成长值变动类型
        self::admin_add_growth,self::admin_reduce_growth,self::recharge_give_growth,self::sign_give_growth, self::order_give_growth
    ];

    const earnings_change = [   //佣金变动
        self::withdraw_dec_earnings, self::withdraw_back_earnings, self::distribution_inc_earnings, self::admin_inc_earnings, self::admin_reduce_earnings
    ];

    public static function getAcccountDesc($from = true){
        $desc = [
            self::admin_add_money               => '系统增加余额',
            self::admin_reduce_money            => '系统扣减余额',
            self::recharge_money                => '用户充值余额',
            self::admin_add_integral            => '系统增加小元币',
            self::admin_reduce_integral         => '系统扣减小元币',
            self::sign_in_integral              => '每日签到赠送小元币',
            self::recharge_give_integral        => '充值赠送小元币',
            self::order_add_integral            => '下单赠送小元币',
            self::order_deduction_integral      => '下单小元币抵扣',
            self::register_add_integral         => '注册赠送小元币',
            self::invite_add_integral           => '邀请会员赠送小元币',
            self::admin_add_growth              => '系统增加成长值',
            self::admin_reduce_growth           => '系统扣减成长值',
            self::sign_give_growth              => '每日签到赠送成长值',
            self::recharge_give_growth          => '充值赠送成长值',
            self::balance_pay_order             => '下单扣减余额',
            self::cancel_order_refund           => '取消订单退回余额',
            self::after_sale_refund             => '售后退回余额',
            self::withdraw_to_balance           => '佣金提现',
            self::withdraw_dec_earnings         => '提现扣减佣金',
            self::withdraw_back_earnings        => '拒绝提现返还佣金',
            self::distribution_inc_earnings     => '订单结算获得佣金',
            self::cancel_order_refund_integral  => '取消订单退回小元币',
            self::deduct_order_first_integral   => '扣除首单小元币',
            self::luck_draw_integral            => '小元币抽奖中奖',
            self::order_goods_give_integral     => '购买商品赠送小元币',
            self::user_transfer_inc_balance     => '会员转账(收入方)',
            self::user_transfer_dec_balance     => '会员转账(支出方)',
            self::order_give_growth             => '下单赠送成长值',
            self::admin_inc_earnings            => '后台增加佣金',
            self::admin_reduce_earnings         => '后台减少佣金',
            self::luck_draw_dec_integral        => '小元币抽奖消耗小元币',
            self::luck_draw_inc_balance         => '小元币抽奖中奖余额',

            self::check_height                  => '记身高赠送小元币',
            self::check_weight                  => '记体重赠送小元币',
            self::order_send                    => '下单赠送小元币',
            self::invite_user                   => '邀请好友赠送小元币',
            self::community_like                => '动态点赞赠送小元币',
            self::community_comment             => '动态评论赠送小元币',
            self::share_article                 => '分享日志赠送小元币',
            self::watch_video                   => '观看视频赠送小元币',
        ];
        if($from === true){
            return $desc;
        }
        return $desc[$from] ?? '';
    }
    //返回变动类型
    public static function getChangeType($from){
        $type = '';
        if(in_array($from,self::money_change)){
            $type = 'money';
        }
        if(in_array($from,self::integral_change)){
            $type = 'integral';
        }
        if(in_array($from,self::growth_change)){
            $type = 'growth';
        }
        if(in_array($from,self::earnings_change)){
            $type = 'earnings';
        }
        return $type;
    }

    public static function getRemarkDesc($from,$source_sn,$remark =''){
        return $remark;
    }


    public static function getChangeAmountAttr($value,$data){
        $amount = $value;
        if(!in_array($data['source_type'],self::money_change)){
            $amount = intval($value);
        }
        if($data['change_type'] == 1){
            return '+'.$amount;
        }
        return '-'.$amount;
    }

    public static function getSourceTypeAttr($value,$data){
        return self::getAcccountDesc($value);

    }

    public static function getcreateTimeAttr($value,$data){
        if($value){
            return date('Y-m-d H:i:s',$value);
        }
        return '';
    }

}