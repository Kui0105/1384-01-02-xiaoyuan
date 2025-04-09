<?php


namespace app\admin\validate;
use think\Db;
use think\Validate;

class AdjustUserLevel extends Validate{
    protected $rule = [
        'id'       => 'require',
        'level'    => 'require|checkLevel',
        'remark'   => 'max:100'
    ];

    protected $message = [
        'id'            => '请选择用户',
        'level'         => '请选择会员等级',
        'remark'        => '备注不能超过100个字符',
    ];

    protected function checkLevel($value,$rule,$data){

        $level = Db::name('user_level')->where(['del'=>0,'id'=>$value])->find();
        // 无等级或已存在的等级
        if($level || $level == 0) {
            return true;
        }
        
        return '该会员等级已不存在，请重新选择';
    }
}