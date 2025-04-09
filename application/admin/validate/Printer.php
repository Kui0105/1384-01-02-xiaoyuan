<?php

namespace app\admin\validate;
use think\Db;
use think\Validate;

class Printer extends Validate{
    protected $rule = [
        'id'            => 'require',
        'type'          => 'require|checkType',
        'name'          => 'require',
        'machine_code'  => 'require|unique:printer,machine_code^del^type',
        'private_key'   => 'require',
        'print_number'  => 'require',
    ];
    public function sceneAdd()
    {
        return $this->remove('id');
    }

    public function sceneDel()
    {
        return $this->only(['id']);
    }

    public function sceneConfig(){
        return $this->only(['id'])->append('id','checkPrinter');
    }

    protected $message = [
        'id.require'            => '请选择打印机',
        'type.require'          => '请选择打印机类型',
        'name.require'          => '请输入打印机名称',
        'machine_code.require'  => '请输入终端号',
        'machine_code.unique'   => '终端号重复',
        'private_key.require'   => '请输入秘钥',
        'print_number.require'  => '请输入打印联数',

    ];


    protected function checkType($value,$rule,$data){
        $type = Db::name('printer_config')->where(['id'=>$value])->find();
        if(!$type){
            return '打印机配置错误';
        }
        if(!$type['client_id'] || !$type['client_secret']){
            return '请先设置'.$type['name'].'的配置';
        }

        return true;

    }

    protected function checkPrinter($value,$rule,$data){
        $printer = Db::name('printer')->where(['id'=>$value])->find();
        if(!$printer || !$printer['machine_code']){
            return '打印机配置错误';
        }

        $type = Db::name('printer_config')->where(['id'=>$printer['type']])->find();

        if(!$type){
            return '打印配置错误';
        }
        if(!$type['client_id'] || !$type['client_secret']){
            return '请先设置'.$type['name'].'的配置';
        }
        return true;
    }
}