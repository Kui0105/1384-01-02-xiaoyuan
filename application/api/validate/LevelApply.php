<?php



namespace app\api\validate;


use think\Db;
use think\Validate;

class LevelApply extends Validate
{
    protected $rule = [
        'user_id'   => 'require',
        'name'      => 'require',
        'mobile'    => 'require|mobile',
        'level'     => 'require|in:2,3,4',
        'area_id'   => 'requireIf:level,2,3',
        'latitude'  => 'require',
        'longtude'  => 'require',
        'address'   => 'require',
    ];

    protected $message = [
        'user_id.require'   => '缺少邀请人',
        'name.require'      => '请填写姓名',
        'mobile.require'    => '请填写手机号码',
        'mobile.mobile'     => '请填写正确的手机号码',
        'level.require'     => '级别错误',
        'level.in'          => '级别错误',
        'area_id.requireIf' => '请选择区域',
        'latitude.require'  => '请选择区域',
        'longtude.province' => '请选择区域',
        'address.require'   => '缺少地址信息',
    ];


    // protected function apply($user_id, $other, $data)
    // {
    //     $result = Db::name('distribution_member_apply')
    //         ->where('user_id', $user_id)
    //         ->where('status', 0)
    //         ->find();
    //     if ($result) {
    //         return '正在审核中，请勿重复提交';
    //     }
    //     return true;
    // }
}