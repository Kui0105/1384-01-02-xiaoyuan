<?php


namespace app\admin\validate;

use think\Validate;
use app\admin\model\HelpCategory;

class Help extends Validate
{

    protected $rule = [
        'id'        => 'require',
        'title'     => 'require|unique:help,title^del',
        // 'cid'       => 'require|checkCid',
    ];

    protected $message = [
        'id.require'        => 'id不可为空',
        'title.require'     => '请输入文章标题',
        'title.unique'      => '标题不能重复！',
        // 'cid.require'       => '请选择帮助分类！',
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


    /**
     * @notes 校验分类
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/6/15 16:03
     */
    protected function checkCid($value, $rule, $data)
    {
        $cate = HelpCategory::where(['id' => $value])->find();
        if (empty($cate)) {
            return '所选分类不存在';
        }
        if ($cate["is_show"] != 1) {
            return '该分类已被停用';
        }
        return true;
    }


}