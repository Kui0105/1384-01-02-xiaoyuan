<?php



namespace app\api\validate;


use think\Validate;

class MnpLogin extends Validate
{
    protected $rule = [
        'code' => 'require',
    ];
}