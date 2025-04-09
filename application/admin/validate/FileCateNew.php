<?php


namespace app\admin\validate;

use think\Db;
use think\Validate;

class FileCateNew extends Validate
{

    protected $rule = [
        'name' => 'require|length:2,20',
        'sort' => 'require|integer|gt:0'
    ];

    protected $message = [
        'name.require' => '分类名称不能为空！',
        'name.length' => '分类名称须在2-10个汉字之间',
        'sort.require' => '排序值不能为空',
        'sort.integer' => '排序值须为整数',
        'sort.gt' => '排序值须大于0',
    ];
}