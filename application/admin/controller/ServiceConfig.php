<?php

namespace app\admin\controller;
use app\admin\logic\ServiceConfigLogic;

class ServiceConfig extends AdminBase
{
    public function config() {
        if($this->request->isAjax() || $this->request->isPost()) {
            $post = $this->request->post();
            ServiceConfigLogic::setConfig($post);
            return $this->_success('设置成功');
        }
        
        return $this->fetch('', ServiceConfigLogic::getConfig());
    }
}