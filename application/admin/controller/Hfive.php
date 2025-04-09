<?php


namespace app\admin\controller;

/**
 * H5商城控制器
 * Class Hfive
 * @package app\admin\controller
 */
class Hfive extends AdminBase
{
    /**
     * @notes H5商城设置
     * @return \think\response\View|void
     * @author Tab
     * @date 2021/8/14 14:55
     */
    public function set()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            \app\admin\logic\Hfive::set($params);
            return $this->_success('设置成功');
        }
        $config = \app\admin\logic\Hfive::getConfig();
        $this->assign('config', $config);
        return view();
    }
}