<?php

namespace app\doctor\controller;

/**
 * 儿童档案（医生端）
 */
class Baby extends DoctorBase
{
    public function index()
    {
        return $this->fetch();
    }

    public function edit()
    {
        return $this->fetch();
    }

    public function report()
    {
        return $this->fetch();
    }
}
