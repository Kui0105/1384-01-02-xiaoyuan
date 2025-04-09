<?php

namespace app\common\logic;

use app\common\model\Distribution;
use app\common\model\DistributionLevel;
use app\common\server\ConfigServer;

/**
 * 分销基础信息逻辑层
 * Class DistributionLogic
 * @package app\common\logic
 */
class DistributionLogic
{
    /**
     * @notes 添加分销基础信息记录
     * @param $userId
     * @author Tab
     */
    public static function add($userId)
    {
        // 默认分销会员等级
        $defaultLevelId = DistributionLevel::where('is_default', 1)->value('id');
        // 分销会员开通方式
        $apply_condition = ConfigServer::get('distribution', 'apply_condition', 2);
        $isDistribution = $apply_condition == 1 ? 1 : 0;

        $data = [
            'user_id' => $userId,
            'level_id' => $defaultLevelId,
            'is_distribution' => $isDistribution,
            'is_freeze' => 0,
            'remark' => '',
        ];

        if($isDistribution) {
            // 成为分销会员时间
            $data['distribution_time'] = time();
        }

        Distribution::create($data);
    }
}