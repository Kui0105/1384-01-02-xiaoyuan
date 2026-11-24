<?php

namespace app\admin\controller;

/**
 * 医生管理（前端页面注入，数据使用 mock）
 * 导航位置：社区 与 内容 之间
 */
class Doctor extends AdminBase
{
    /**
     * 医生列表
     */
    public function lists()
    {
        return $this->fetch();
    }

    /**
     * 医生新增/编辑弹窗
     */
    public function edit()
    {
        return $this->fetch();
    }

    /**
     * 儿童档案（医生端创建的档案，管理端只查看）
     */
    public function baby()
    {
        return $this->fetch();
    }

    /**
     * 测评报告详情
     */
    public function report()
    {
        return $this->fetch();
    }

    /**
     * 诊所列表
     */
    public function clinic()
    {
        return $this->fetch();
    }

    /**
     * 诊所新增/编辑弹窗
     */
    public function clinicEdit()
    {
        return $this->fetch();
    }
}
