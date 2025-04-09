<?php

namespace app\api\validate;
use think\Validate;

class changeUserInfo extends Validate{
    protected $rule = [
        'nickname'  => 'require',
        'sex'       => 'require|in:0,1,2',
    ];
    protected $message = [
        'nickname.require'  => '请输入昵称',
        'sex.require'       => '请选择性别',
        'sex.in'            => '性别设置错误',
    ];
    public function scenePc(){
        $this->only(['nickname','sex']);
    }
}