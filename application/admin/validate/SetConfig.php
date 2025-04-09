<?php

namespace app\admin\validate;
use think\Validate;

class SetConfig extends Validate{
    protected $rule = [
        'id'            => 'require',
        'client_id'     => 'require',
        'client_secret' => 'require',
    ];
    protected $message = [
        'id.require'            => '请选择打印机配置',
        'client_id.require'     => '请输入应用ID',
        'client_secret.require' => '请输入应用秘钥',
    ];
}