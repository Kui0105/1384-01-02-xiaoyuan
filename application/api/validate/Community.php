<?php


namespace app\api\validate;

use think\Db;
use think\Validate;

class Community extends Validate
{
    protected $rule = [
        'name'      => 'require',
        'content'   => 'require|checkLength',
    ];

    protected $message = [
        'name.require'      => '请输入标题',
        'content.require'   => '请填写内容',
        'latitude.require'  => '缺少地址信息',
        'longitude.require' => '缺少地址信息',
        'address.require'   => '缺少地址信息',
    ];


    protected function checkLength($value, $rule, $data)
    {
        if(mb_strlen($value, 'UTF-8') >=2000){
            return '内容超长，2000字以内';
        }
        return true;
    }

}
