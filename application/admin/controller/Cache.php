<?php



namespace app\admin\controller;

/**
 * 系统缓存
 * Class Cache
 * @package app\admin\controller
 */
class Cache extends AdminBase
{

    public function cache()
    {
        if ($this->request->isAjax()) {
            \think\facade\Cache::clear();
            $this->_success('清除成功');
        }
        return $this->fetch();
    }
}