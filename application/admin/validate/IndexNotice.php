<?php

namespace app\admin\validate;

use think\Validate;

class IndexNotice extends Validate
{

    protected $rule = [
        'id'        => 'require',
        'title'     => 'require|unique:index_notice,title^del',
    ];

    protected $message = [
        'id.require'        => 'id不可为空',
        'title.require'     => '请输入文章标题',
        'title.unique'      => '标题不能重复！',
    ];
    protected function sceneAdd()
    {
        $this->remove('id');

    }

    protected function sceneEdit()
    {

    }

    public function sceneDel()
    {
        $this->only(['id']);
    }


}