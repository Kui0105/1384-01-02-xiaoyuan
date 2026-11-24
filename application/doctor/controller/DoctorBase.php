<?php

namespace app\doctor\controller;

use think\Controller;
use think\facade\Session;

/**
 * 医生端基类控制器
 * 负责独立会话校验（与管理端 admin_info 完全隔离）
 */
class DoctorBase extends Controller
{
    // 免登录的方法
    public $like_not_need_login = [];

    protected function initialize()
    {
        parent::initialize();

        $action = strtolower($this->request->action());
        $free = array_map('strtolower', $this->like_not_need_login);
        if (in_array($action, $free)) {
            return;
        }

        // 未登录跳转医生端登录页
        if (!Session::get('doctor_info')) {
            $this->redirect('/doctor/login/index.html');
        }
    }

    /**
     * 成功响应
     */
    protected function _success($msg = '', $data = [], $code = 1)
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => $data]);
    }

    /**
     * 失败响应
     */
    protected function _error($msg = '', $data = [], $code = 0)
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => $data]);
    }

    /**
     * 当前医生信息
     */
    protected function doctorInfo()
    {
        return Session::get('doctor_info');
    }
}
