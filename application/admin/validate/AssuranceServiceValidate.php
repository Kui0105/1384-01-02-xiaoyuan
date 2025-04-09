<?php



namespace app\admin\validate;


use think\Validate;

class AssuranceServiceValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'title' => 'require',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'title.require' => '服务名称不能为空',
    ];


    public function sceneAdd()
    {
        return $this->only(['title']);
    }

    public function sceneEdit()
    {
        return $this->only(['id','title']);
    }
}