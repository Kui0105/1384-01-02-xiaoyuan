<?php


namespace app\admin\model;

use think\Model;

/**
 * 分销会员申请
 * Class DistributionMemberApply
 * @package app\common\model
 */
class DistributionMemberApply extends Model
{
    /**
     * 分销会员申请状态
     */
    const STATUS_WAIT_AUDIT    = 0; //待审核
    const STATUS_AUDIT_SUCCESS = 1; //审核通过
    const STATUS_AUDIT_ERROR   = 2; //审核拒绝

    public function user()
    {
        return $this->hasOne('app\common\model\User', 'id', 'user_id');
    }

    public static function getApplyStatus($status = true)
    {
        $desc = [
            self::STATUS_WAIT_AUDIT    => '待审核',
            self::STATUS_AUDIT_SUCCESS => '审核通过',
            self::STATUS_AUDIT_ERROR   => '审核拒绝',
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }
}