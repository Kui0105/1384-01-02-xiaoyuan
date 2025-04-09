<?php

namespace app\admin\controller;
use app\admin\logic\MapConfigLogic;

class MapConfig extends AdminBase{

    /**
     * @notes 获取地图配置
     * @return mixed|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author ljj
     * @date 2021/8/20 8:34 下午
     */
    public function config(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            MapConfigLogic::setConfig($post);
            return $this->_success('设置成功',[]);
        }
        $this->assign('config',MapConfigLogic::getConfig());
        return $this->fetch();
    }
}