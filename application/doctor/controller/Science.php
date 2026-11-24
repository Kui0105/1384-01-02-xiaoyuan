<?php

namespace app\doctor\controller;

/**
 * 成长科普（医生端）
 */
class Science extends DoctorBase
{
    public function index()
    {
        return $this->fetch();
    }

    public function detail()
    {
        return $this->fetch();
    }
}
