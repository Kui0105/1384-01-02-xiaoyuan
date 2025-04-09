<?php

namespace app\admin\validate;
use think\Db;
use think\Validate;


class OrderPrint extends Validate{
    protected $rule = [
        'id'        => 'require|checkPrint',
    ];

    protected function checkPrint($value,$rule,$data){
        $printer_config = Db::name('printer_config')
                    ->where(['status'=>1])
                    ->find();

        if(!$printer_config){
            return '请先到小票打印里面配置打印设置';
        }
        $printer = Db::name('printer')
                    ->where(['type'=>$printer_config['id']])
                    ->find();
        if(!$printer){
            return '请先添加打印机';
        }
        return true;

    }
}