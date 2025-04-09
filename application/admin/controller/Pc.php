<?php


namespace app\admin\controller;

/**
 * PC商城控制器
 * Class Pc
 * @package app\admin\controller
 */
class Pc extends AdminBase
{
    /**
     * @notes PC商城设置
     * @return \think\response\View|void
     * @author Tab
     * @date 2021/8/14 14:55
     */
    public function set()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            \app\admin\logic\Pc::set($params);
            return $this->_success('设置成功');
        }
        $config = \app\admin\logic\Pc::getConfig();
        $this->assign('config', $config);
        return view();
    }
}