<?php

namespace app\doctor\controller;

/**
 * 视频课堂（医生端）
 */
class Video extends DoctorBase
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
