<?php


namespace app\admin\validate;

use think\Validate;

class Community extends Validate
{

    protected $rule = [
        'id'    => 'require',
        'name'  => 'require',
        'content' => 'require',
        'imgs'   => 'array',
    ];

    protected $message = [
        'id.require'    => 'id不可为空',
        'name.require'  => '请输入标题',
        'content.require'   => '请填写内容！',
        'imgs.array'  => '请上传图片',
    ];


    protected function sceneEdit()
    {
        $this->remove('type');
    }

    public function sceneDel()
    {
        $this->only(['id']);
    }

}