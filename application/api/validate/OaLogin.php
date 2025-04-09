<?php



namespace app\api\validate;


use think\Validate;

class OaLogin extends Validate
{
    protected $rule = [
        'code' => 'require',
    ];
}