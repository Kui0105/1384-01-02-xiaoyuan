<?php


namespace app\admin\validate;
use think\Db;
use think\Validate;

class AdjustProvince extends Validate{
    protected $rule = [
        'id'       => 'require',
        'level'    => 'require|checkProvinceId',
        'remark'   => 'max:100'
    ];

    protected $message = [
        'id'            => '请选择用户',
        'level'         => '请选择地区',
        'remark'        => '备注不能超过100个字符',
    ];

    protected function checkProvinceId($value,$rule,$data){

        $if_use = Db::name('user_new_level')->where('self_id',$value)->count();
        // 无等级或已存在的等级
        if($if_use) {
            return '该区域已有代理';
        }
        return true;
    }
}