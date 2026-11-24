<?php

namespace app\doctor\controller;

use think\facade\Cache;

/**
 * 医生端主框架 & 工作台
 */
class Index extends DoctorBase
{
    /**
     * 主框架
     */
    public function index()
    {
        $doctor = $this->doctorInfo();
        $this->assign('doctor', $doctor);
        $this->assign('doctor_initial', mb_substr($doctor['name'], 0, 1, 'utf-8'));
        return $this->fetch();
    }

    /**
     * 工作台（数据统计展示）
     */
    public function stat()
    {
        return $this->fetch();
    }

    /**
     * 个人信息
     */
    public function profile()
    {
        $doctor = $this->doctorInfo();
        $this->assign('doctor', $doctor);
        $this->assign('doctor_initial', mb_substr($doctor['name'], 0, 1, 'utf-8'));
        return $this->fetch();
    }

    /**
     * 修改密码页
     */
    public function password()
    {
        return $this->fetch();
    }

    /**
     * 提交修改密码
     * 校验：原密码正确、新密码与重复新密码一致、新密码不为空
     */
    public function changePassword()
    {
        $oldPassword = trim(input('post.old_password', ''));
        $newPassword = trim(input('post.new_password', ''));
        $confirmPassword = trim(input('post.confirm_password', ''));

        $current = Cache::get('doctor_mock_password') ?: '123456';

        if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return $this->_error('请填写完整信息');
        }

        if ($oldPassword !== $current) {
            return $this->_error('原密码不正确');
        }

        if ($newPassword !== $confirmPassword) {
            return $this->_error('两次输入的新密码不一致');
        }

        if (strlen($newPassword) < 6) {
            return $this->_error('新密码长度不能少于 6 位');
        }

        if ($newPassword === $oldPassword) {
            return $this->_error('新密码不能与原密码相同');
        }

        // 持久化新密码（Mock）
        Cache::set('doctor_mock_password', $newPassword);

        return $this->_success('密码修改成功，请使用新密码重新登录');
    }
}
