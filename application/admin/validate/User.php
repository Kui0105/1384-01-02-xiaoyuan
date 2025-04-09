<?php


namespace app\admin\validate;
use think\Validate;

class User extends Validate{

    protected $rule = [
        'id'        => 'require',
        'nickname'  => 'require',
        'avatar'    => 'require',
        'password'  => 'length:6,16',
//        'mobile'    => "mobile|unique:user,mobile^del"
        'mobile'    => "mobile|checkMobile",
        'disable'   => 'require|in:0,1'
    ];

    protected $message = [
        'id.require'       => '请选择会员',
        'nickname.require' => '请输入会员昵称',
        'avatar.require'   => '请输入会员头像',
        'password'         => '密码长度为6~16位',
        'mobile.mobile'    => '请输入正确手机号',
        'mobile.unique'    => '手机号已被使用',
        'disable.require'  => '请选择禁用状态',
        'disable.in'       => '禁用状态参数错误',
    ];


    protected function checkMobile($value, $rule, $data)
    {
        $user = \app\admin\model\User::where([
            ['id', '<>', $data['id']],
            ['mobile', '=', $value],
            ['del', '=', 0]
        ])->find();

        if ($user) {
            return '手机号已被使用';
        }
        return true;
    }
}