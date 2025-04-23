<?php


namespace app\api\validate;

use think\Db;
use think\Validate;

class Baby extends Validate
{
    protected $rule = [
        'id'        => 'require',
        'date'      => 'require',
        'name'      => 'require',
        'head_img'  => 'require',
        'sex'       => 'require|in:1,2',
        'birthday'  => 'require',
        'father_height' => 'require',
        'mother_height' => 'require',
        'sup_height'    => 'require',
        'height'    => 'require',
        'weight'    => 'require',
        'province_id'   => 'require',
        'city_id'       => 'require'
    ];

    protected $message = [
        'id.require'  => '请选择宝宝',
        'date.require'  => '请选择测评日期',
        'name.require'  => '请输入姓名',
        'head_img.require'  => '请上传头像',
        'sex.require'   => '请选择性别',
        'birthday.require' => '请选择生日',
        'father_height.require' => '请输入父亲身高',
        'mother_height.require' => '请输入母亲身高',
        'sup_height.require'    => '请输入期望身高',
        'height.require'    => '请输入身高',
        'weight.require'    => '请输入体重',
        'sex.in'        => '性别错误',
        'province_id.require'   => '请选择城市',
        'city_id.require'   => '请选择城市'
    ];


    public function sceneAdd()
    {
        $this->only(['name', 'head_img','sex','birthday','father_height','mother_height','sup_height','height','weight','province_id','city_id']);
    }

    public function sceneEdit()
    {
        $this->only(['name', 'head_img','sex','birthday','father_height','mother_height','sup_height','province_id','city_id']);
    }

    public function sceneHeightCheck()
    {
        $this->only(['id','date','height','weight']);
    }
}