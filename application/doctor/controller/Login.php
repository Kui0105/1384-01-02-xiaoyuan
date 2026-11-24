<?php

namespace app\doctor\controller;

use think\facade\Session;
use think\facade\Cache;

/**
 * 医生端登录入口（独立，与管理端 account/login 区分）
 */
class Login extends DoctorBase
{
    public $like_not_need_login = ['index', 'check', 'logout', 'captcha'];

    /**
     * 登录页
     */
    public function index()
    {
        if (Session::get('doctor_info')) {
            $this->redirect('/doctor/index/index.html');
        }
        return $this->fetch();
    }

    /**
     * 登录校验（Mock 固定账号）
     */
    public function check()
    {
        if (!$this->request->isAjax()) {
            return $this->_error('非法请求');
        }

        $account = trim(input('post.account', ''));
        $password = trim(input('post.password', ''));

        // ===== Mock 账号（密码从缓存读取，支持修改密码后持久化）=====
        $mockAccount = '13800138001';
        $mockPassword = Cache::get('doctor_mock_password') ?: '123456';

        if ($account === '' || $password === '') {
            return $this->_error('请输入账号和密码');
        }

        if ($account !== $mockAccount || $password !== $mockPassword) {
            return $this->_error('账号或密码错误');
        }

        // 写入医生端独立会话
        Session::set('doctor_info', [
            'id'         => 1001,
            'name'       => '张敏',
            'account'    => $mockAccount,
            'clinic_id'  => 1,
            'clinic_name'=> '天使儿童诊所（总院）',
            'login_time' => date('Y-m-d H:i:s'),
        ]);

        return $this->_success('登录成功');
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        Session::delete('doctor_info');
        $this->redirect('/doctor/login/index.html');
    }

    /**
     * 验证码（Mock，返回固定值，前端仅展示）
     */
    public function captcha()
    {
        return $this->_success('ok', ['code' => '8888']);
    }
}
