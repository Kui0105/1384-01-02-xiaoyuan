<?php


namespace app\api\validate;


use app\common\model\LuckdrawRecord;
use app\common\server\ConfigServer;
use think\Validate;
use app\common\model\User;


class Luckdraw extends Validate
{

    protected $rule = [
        'user_id' => 'require|checkActivity',
    ];

    protected $message = [
        'user_id.require' => '参数缺失',
    ];

    public static function checkActivity($value, $rule, $data)
    {
        $user = User::findOrEmpty($value);
        if ($user->isEmpty()) {
            return '用户不存在';
        }

        // 活动是否进行中
        $status = ConfigServer::get('luckdraw', 'status', 0);
        if ($status == 0) {
            return '抽奖活动已结束';
        }

        // 每次抽奖需要消耗积分
        $need = ConfigServer::get('luckdraw', 'need', 0);
        if ($user['user_integral'] < $need) {
            return '积分不足';
        }

        // 计算用户剩余抽奖次数  该用户今天抽奖次数
        $recordCount = LuckdrawRecord::where(['user_id' => $value])
            ->whereTime('create_time', 'today')
            ->count('id');

        // 每日抽奖次数
        $limit = ConfigServer::get('luckdraw', 'limit', 0);
        if ($recordCount >= $limit) {
            return '今天抽奖次数已用完';
        }

        return true;
    }

}