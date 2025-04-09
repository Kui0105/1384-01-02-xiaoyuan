<?php


namespace app\admin\validate;


use think\Validate;
use app\common\model\Luckdraw as LuckdrawModel;

class LuckDraw extends Validate
{
    protected $rule = [
        'id'          => 'require|number',
        'prize_type'  => 'require|number|checkPrizeValue',
        'sort'        => 'require|number',
        'probability' => 'require',
        'status'      => 'require|in:0,1'
    ];

    protected $message = [
        'id.require'          => 'id不可为空',
        'prize_type.require'  => '请选择抽奖类型',
        'sort.require'        => '请填写排序号',
        'sort.number'         => '排序号需为数字',
        'probability.require' => '请填写抽奖概率',
        'status.require'  => '请选择状态',
        'status.in'       => '状态选择不在范围内',
    ];

    /**
     * Notes: 添加场景
     * @author 张无忌(2020/12/24 16:48)
     */
    public function sceneAdd()
    {
        $this->remove('id');
    }


    /**
     * @notes 验证是否填写奖品
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/2/15 11:58
     */
    protected function checkPrizeValue($value, $rule, $data)
    {
        if ($value == LuckdrawModel::PRIZE_INTEGRAL) {
            if (!isset($data['integral']) || $data['integral'] < 0) {
                return '请填写奖品数量';
            }
            return true;
        }

        if ($value == LuckdrawModel::PRIZE_COUPON && empty($data['coupon'])) {
            return '请选择奖品';
        }

        if ($value == LuckdrawModel::PRIZE_INTEGRAL && empty($data['balance'])) {
            if (!isset($data['balance']) || $data['balance'] < 0) {
                return '请填写奖品金额';
            }
        }
        return true;
    }


}