<?php


namespace app\admin\controller;

use app\admin\logic\UserSettingLogic;

/**
 * 用户设置
 * Class User
 * @package app\admin\controller\setting
 */
class UserSetting extends AdminBase
{
    /**
     * @notes 用户设置
     * @return \think\response\View
     * @author Tab
     */
    public function index()
    {
        $config = UserSettingLogic::getConfig();
        return view('', ['config' => $config,'user_level'=>UserSettingLogic::getUserLevel()]);
    }

    /**
     * @notes 用户设置
     * @return \think\response\Json
     * @author Tab
     */
    public function set()
    {
        $params = $this->request->post();
        $result = UserSettingLogic::set($params);
        if($result) {
            return $this->_success('保存成功');
        }
        return $this->_error(UserSettingLogic::getError());
    }
}