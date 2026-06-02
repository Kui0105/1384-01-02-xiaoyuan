<?php

namespace app\api\logic;
use app\common\logic\AccountLogLogic;
use app\common\model\AccountLog;
use app\common\server\UrlServer;
use think\Db;
use think\Exception;
use think\helper\Time;
use app\common\server\ConfigServer;
class SignLogic
{
    /**
     * note 每日签到
     * create_time 2020/12/3 16:06
     */
    public static function lists($user_id){

        //用户信息
        $user = Db::name('user')
                ->where(['id'=>$user_id])
                ->field('id,nickname,avatar,user_integral')
                ->find();

        $user['avatar'] = UrlServer::getFileUrl($user['avatar']);
        $user['today_sign'] = 0;
        $today_sign = Db::name('user_sign')
            ->where(['del'=>0 , 'user_id'=>$user_id])
            ->whereTime('sign_time', 'today')
            ->find();

        list($today_start,$today_end) = Time::today();
        //今天是否已签到
        $today_sign && $user['today_sign'] = 1;
        //今天签到赠送的积分
        $today_sign_integral = 0;
        //昨天是否签到
        $yester_sign = Db::name('user_sign')
                ->where(['del'=>0 , 'user_id'=>$user_id])
                ->whereTime('sign_time', 'yesterday')
                ->find();
        //昨天没签到，则签到中断重新计算连续天数
        if(!$yester_sign){
            Db::name('user_sign')
                ->where(['del'=>0,'user_id'=>$user_id])
                ->where('sign_time','<',$today_start)
                ->update(['del'=> 1,'update_time'=>time()]);
        }

        // 获取当前月份的开始和结束时间戳
        list($monthStart,$monthEnd) = Time::month();
        // 查询当月的签到记录
        $sign_all = Db::name('user_sign')
            ->where(['del' => 0, 'user_id' => $user_id])
            ->whereBetweenTime('sign_time', date('Y-m-d', $monthStart), date('Y-m-d', $monthEnd))
            ->order('sign_time asc')
            ->select();

        // 将签到日期转换为数组便于判断
        $signedDays = [];
        foreach ($sign_all as $sign) {
            $day = date('j', $sign['sign_time']); // 获取日期中的天数（不补零）
            $signedDays[$day] = 1;
        }

        // 获取当前月份的天数
        $daysInMonth = date('t', $monthStart);

        // 构建每月签到状态列表
        $sign_list = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $sign_status = isset($signedDays[$i]) ? 1 : 0;
            $sign_list[] = [
                'days'   => $i,
                'status' => $sign_status,
            ];
        }

        //签到规则
        // $sign_list = Db::name('sign_daily')
        //         ->where(['del'=>0])
        //         ->order('type asc,days asc')
        //         ->column('*','days');
        // $sign_total_days = '';
        // $days_list = [];
        // if($sign_list){
        //     $start_sign = current($sign_list);      //第一次签到规则
        //     $end_sign = end($sign_list);     //最后一次签到规则
        //     //每天赠送的积分
        //     $everyday_send_integral = 0;
        //     $start_sign['integral_status'] && $everyday_send_integral = $start_sign['integral'];
        //     //累计签到的总天数
        //     $sign_total_days = Db::name('user_sign')
        //         ->where(['del'=>0,'user_id'=>$user_id])
        //         ->order('id desc')
        //         ->value('days');
        //
        //
        //     for($days = 1; $days <= $end_sign['days'] ;$days++){
        //         $send_integral = $everyday_send_integral;
        //         //连接签到赠送的积分
        //         if(isset($sign_list[$days]) && $sign_list[$days]['integral_status'] ){
        //             $send_integral = $everyday_send_integral + $sign_list[$days]['integral'];
        //         }
        //         //合并数据
        //         $days_list[$days] = [
        //             'days'          => $days,
        //             'status'        => 0,
        //             'integral'      => $send_integral,
        //             'growth'        => 0,
        //         ];
        //         $total_sin_days = $end_sign['days'];  //可连续签到的最大天数
        //         //更新签到天数之前的签到状态
        //         if($days === $sign_total_days){
        //             $today_sign_integral = $send_integral;//今天签到获得的积分
        //             for ($sign_day = $days;$sign_day >= 1;$sign_day--){
        //                 $days_list[$sign_day]['status'] = 1;
        //             }
        //         }
        //         //如果连续签到天数大于总天数，则全部标记为已签到状态
        //         if($sign_total_days > $total_sin_days){
        //             $days_list[$days]['status'] = 1;
        //         }
        //     }
        //
        // }
        // $user['days'] = $sign_total_days ?: 0;

        $user['days'] = count($sign_all);
        //赚积分
        $make_inegral = [];
        $make_inegral[] = [
            'name'      => '每日签到',
            'status'    => $user['today_sign'],
            'integral'  => $today_sign_integral,
            'type'      => 1,//类型，主要用前端显示图标
        ];
        $height_status = true;
        $height_p = ConfigServer::get('point_way','height_limit',0);
        $height_int = ConfigServer::get('point_way','height',0);
        // if($height_p){
            $now_num = AccountLog::where(['source_type'=>AccountLog::check_height,'user_id'=>$user_id])->whereTime('create_time', 'month')->count();
            if(!$now_num){
                $height_status = false;
            }
        //     if($height_p > $now_num){
        //         $height_status = false;
        //     }
        // }
        $make_inegral[] = [
            'name'      => '记身高',
            'status'    => $height_status,
            'integral'  => $height_int,
            'type'      => 2,//类型，主要用前端显示图标
        ];
        $order_status = true;
        $order_p = ConfigServer::get('point_way','order_limit',0);
        $order_int = ConfigServer::get('point_way','order',0);
        if($order_p){
            $now_num = AccountLog::where(['source_type'=>AccountLog::order_send,'user_id'=>$user_id])->whereTime('create_time', 'today')->count();
            if($order_p > $now_num){
                $order_status = false;
            }
        }
        $make_inegral[] = [
            'name'      => '下单任意商品',
            'status'    => $order_status,
            'integral'  => $order_int,
            'type'      => 4,//类型，主要用前端显示图标
        ];
        $invite_status = true;
        $invite_p = ConfigServer::get('point_way','invite_limit',0);
        $invite_int = ConfigServer::get('point_way','invite',0);
        if($invite_p){
            $now_num = AccountLog::where(['source_type'=>AccountLog::invite_user,'user_id'=>$user_id])->whereTime('create_time', 'today')->count();
            if($invite_p > $now_num){
                $invite_status = false;
            }
        }
        $make_inegral[] = [
            'name'      => '成功邀请好友',
            'status'    => $invite_status,
            'integral'  => $invite_int,
            'type'      => 5,//类型，主要用前端显示图标
        ];
        $like_status = true;
        $like_p = ConfigServer::get('point_way','like_limit',0);
        $like_int = ConfigServer::get('point_way','like',0);
        if($like_p){
            $now_num = AccountLog::where(['source_type'=>AccountLog::community_like,'user_id'=>$user_id])->whereTime('create_time', 'today')->count();
            if($like_p > $now_num){
                $like_status = false;
            }
        }
        $make_inegral[] = [
            'name'      => '给朋友动态点赞',
            'status'    => $like_status,
            'integral'  => $like_int,
            'type'      => 6,//类型，主要用前端显示图标
        ];
        $comment_status = true;
        $comment_p = ConfigServer::get('point_way','comment_limit',0);
        $comment_int = ConfigServer::get('point_way','comment',0);
        if($comment_p){
            $now_num = AccountLog::where(['source_type'=>AccountLog::community_comment,'user_id'=>$user_id])->whereTime('create_time', 'today')->count();
            if($comment_p > $now_num){
                $comment_status = false;
            }
        }
        $make_inegral[] = [
            'name'      => '给朋友动态评论',
            'status'    => $comment_status,
            'integral'  => $comment_int,
            'type'      => 7,//类型，主要用前端显示图标
        ];
        $share_status = true;
        $share_p = ConfigServer::get('point_way','share_limit',0);
        $share_int = ConfigServer::get('point_way','share',0);
        if($share_p){
            $now_num = AccountLog::where(['source_type'=>AccountLog::share_article,'user_id'=>$user_id])->whereTime('create_time', 'today')->count();
            if($share_p > $now_num){
                $share_status = false;
            }
        }
        $make_inegral[] = [
            'name'      => '分享长高日志',
            'status'    => $share_status,
            'integral'  => $share_int,
            'type'      => 8,//类型，主要用前端显示图标
        ];
        $video_status = true;
        $video_p = ConfigServer::get('point_way','video_limit',0);
        $video_int = ConfigServer::get('point_way','video',0);
        if($video_p){
            $now_num = AccountLog::where(['source_type'=>AccountLog::watch_video,'user_id'=>$user_id])->whereTime('create_time', 'today')->count();
            if($video_p > $now_num){
                $video_status = false;
            }
        }
        $make_inegral[] = [
            'name'      => '观看长高视频',
            'status'    => $video_status,
            'integral'  => $video_int,
            'type'      => 9,//类型，主要用前端显示图标
        ];


        $order_award_integral = ConfigServer::get('marketing','order_award_integral',0);
        $invited_award_integral = ConfigServer::get('marketing','invited_award_integral',0);
        //下单奖励
        if($order_award_integral > 0){

            $today_order_award = Db::name('account_log')
                ->where(['user_id'=>$user_id,'source_type'=>AccountLog::order_add_integral])
                ->whereTime('create_time',[$today_start,$today_end])
                ->find();
            $make_inegral[] = [
                'name'      => '下单任意商品',
                'status'    => $today_order_award ? 1 : 0,
                'integral'  => $order_award_integral,
                'type'      => 2,
            ];
        }
        //邀请奖励
        if($invited_award_integral > 0){
            $total_invited_award = Db::name('account_log')
                    ->where(['user_id'=>$user_id,'source_type'=>AccountLog::invite_add_integral])
                    ->whereTime('create_time',[$today_start,$today_end])
                    ->find();
            $make_inegral[] = [
                'name'      => '成功邀请1位好友',
                'status'    => $total_invited_award ? 1 : 0,
                'integral'  => $invited_award_integral,
                'type'      => 3,
            ];
        }

        return [
            'user'          => $user,
            // 'sign_list'     => array_values($days_list),
            'sign_list'     => $sign_list,
            'make_inegral'  => $make_inegral,
        ];
    }
    //签到接口
    public static function sign($user_id){
        try{
            Db::startTrans();
            $sign_list = Db::name('sign_daily')
                ->where(['del'=>0,'type'=>2])
                ->order('type asc,days asc')
                ->column('*','days');
            //签到记录
            $last_sign = Db::name('user_sign')
                ->where(['del'=>0,'user_id'=>$user_id])
                ->order('id desc')
                ->find();
            $now = time();
            $total_integral = 0;                    //签到赠送的积分
            $total_growth = 0;                      //签到赠送的成长值
            $continuous_integral = 0;               //连续签到积分
            $continuous_growth = 0;                 //连续签到成长值
            $coupon_id = '';
            $coupon_name = '';
            $sign_day = 1;                  //累计签到天数
            //每天签到的奖励
            $everyday_sign = Db::name('sign_daily')
                ->where(['del'=>0,'type'=>1])
                ->find();

            //有签到记录，说明之前有签到
            if($last_sign){

                $sign_day = $last_sign['days'] + 1;
                $sign = $sign_list[$sign_day] ?? [];
                //累计签到天数,额外奖励
                if($sign){
                    if($sign['integral_status'] && $sign['integral'] > 0){
                        $total_integral+=$sign['integral'];
                        $continuous_integral = $sign['integral'];
                    }
                    if($sign['growth_status'] && $sign['growth'] > 0){
                        $total_growth+=$sign['growth'];
                        $continuous_growth =$sign['growth'];
                    }
                    //赠送优惠券
                    if($sign['coupon_status'] && $sign['coupon_id']){
                        $res = CouponLogic::signSendCoupon($user_id,$sign['coupon_id']);
                        if(!empty($res)){
                            $coupon_name = array_column($res,'name');
                            $coupon_name = implode(',',$coupon_name);
                        }
                        $coupon_id = $sign['coupon_id'];
                    }
                }
                //每天签到奖励
                if($everyday_sign){
                    if($everyday_sign['integral_status'] && $everyday_sign['integral'] > 0){
                        $total_integral+=$everyday_sign['integral'];
                    }
                    if($everyday_sign && $everyday_sign['growth_status'] && $everyday_sign['growth'] > 0){
                        $total_growth+=$everyday_sign['growth'];
                    }
                }
                $sign_add = [
                    'user_id'               => $user_id,
                    'days'                  => $sign_day,
                    'integral'              => $everyday_sign['integral'],
                    'growth'                => $everyday_sign['growth'],
                    'continuous_integral'   => $continuous_integral,
                    'continuous_growth'     => $continuous_growth,
                    'sign_time'             => $now,
                    'create_time'           => $now,
                    'coupon_id'             => $coupon_id,
                ];

            }else{

                //连续一天的奖励
                $one_day_sign = $sign_list['1'] ?? [];
                if($one_day_sign){
                    if($one_day_sign['integral_status'] && $one_day_sign['integral'] > 0){
                        $total_integral+=$one_day_sign['integral'];
                        $continuous_integral = $one_day_sign['integral'];
                    }
                    if($one_day_sign && $one_day_sign['growth_status'] && $one_day_sign['growth'] > 0){
                        $total_growth+=$one_day_sign['growth'];
                        $continuous_growth = $one_day_sign['growth'];
                    }
                    //赠送优惠券
                    if($one_day_sign['coupon_status'] && $one_day_sign['coupon_id']){
                        $res = CouponLogic::signSendCoupon($user_id,$one_day_sign['coupon_id']);
                        if(!empty($res)){
                            $coupon_name = array_column($res,'name');
                            $coupon_name = implode(',',$coupon_name);
                        }
                        $coupon_id = $one_day_sign['coupon_id'];
                    }
                }

                if($everyday_sign){
                    if($everyday_sign['integral_status'] && $everyday_sign['integral'] > 0){
                        $total_integral+=$everyday_sign['integral'];
                    }
                    if($everyday_sign['growth_status'] && $everyday_sign['growth'] > 0){
                        $total_growth+=$everyday_sign['growth'];
                    }
                }
                $sign_add = [
                    'user_id'               => $user_id,
                    'days'                  => $sign_day,
                    'integral'              => $everyday_sign['integral_status'] ? $everyday_sign['integral'] : 0,
                    'continuous_integral'   => $continuous_integral,
                    'growth'                => $everyday_sign['growth_status'] ? $everyday_sign['growth'] : 0,
                    'continuous_growth'     => $continuous_growth,
                    'sign_time'             => $now,
                    'create_time'           => $now,
                    'coupon_id'             => $coupon_id,
                ];
            }
            Db::name('user_sign')->insert($sign_add);

            if($total_integral){
                Db::name('user')
                    ->where(['del'=>0 , 'id'=>$user_id])
                    ->setInc('user_integral',$total_integral);
                AccountLogLogic::AccountRecord($user_id,$total_integral,1, AccountLog::sign_in_integral);
            }
            if($total_growth){
                //用户成长值
                Db::name('user')
                    ->where(['del'=>0 , 'id'=>$user_id])
                    ->setInc('user_growth',$total_growth);
                AccountLogLogic::AccountRecord($user_id,$total_growth,1,AccountLog::sign_give_growth);

            }

            Db::commit();
            return [
                'integral'  => $total_integral,
                'growth'    => $total_growth,
                'days'      => $sign_day,
                'coupon_name'   => $coupon_name
            ];


        }catch (Exception $e){
            Db::rollback();
            return $e->getMessage();

        }


    }
}
