<?php

namespace app\admin\validate;
use think\Validate;

class RechargeTemplate extends Validate{
    protected $rule = [
        'money'          => 'require|gt:0',
    ];
    protected $message = [
        'money.require'     => '请输入充值金额',
        'money.gt'          => '充值金额必须大于零',
    ];
}