<?php


namespace app\admin\controller;

use app\admin\logic\BabyCheckLogic;

class BabyCheck extends AdminBase
{

    /**
     * 骨龄测评列表
     * @return mixed
     */
    public function bone_lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('获取成功', BabyCheckLogic::lists($get,4));
        }
        return $this->fetch();
    }


}