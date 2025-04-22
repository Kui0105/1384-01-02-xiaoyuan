<?php

namespace app\admin\controller;

use app\common\server\ConfigServer;

class BabySetting extends AdminBase
{

    /**
     * 身高设置
     */
    public function height()
    {
        $config = [
            'result_0' => ConfigServer::get('baby_height', 'result_0' ),
            'result_1' => ConfigServer::get('baby_height', 'result_1' ),
            'result_2' => ConfigServer::get('baby_height', 'result_2' ),
            'result_3' => ConfigServer::get('baby_height', 'result_3' ),
            'result_4' => ConfigServer::get('baby_height', 'result_4' ),
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }

    public function setHeight()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('baby_height', 'result_0', $post['result_0']);
            ConfigServer::set('baby_height', 'result_1', $post['result_1']);
            ConfigServer::set('baby_height', 'result_2', $post['result_2']);
            ConfigServer::set('baby_height', 'result_3', $post['result_3']);
            ConfigServer::set('baby_height', 'result_4', $post['result_4']);
            $this->_success('修改成功');
        }
    }

    /**
     * 脾胃设置
     */
    public function spleen()
    {
        $config = [
            'result_0' => ConfigServer::get('baby_spleen', 'result_0' ),
            'result_1' => ConfigServer::get('baby_spleen', 'result_1' ),
            'result_2' => ConfigServer::get('baby_spleen', 'result_2' ),
            'result_3' => ConfigServer::get('baby_spleen', 'result_3' ),
            'result_4' => ConfigServer::get('baby_spleen', 'result_4' ),
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }

    public function setSpleen()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('baby_spleen', 'result_0', $post['result_0']);
            ConfigServer::set('baby_spleen', 'result_1', $post['result_1']);
            ConfigServer::set('baby_spleen', 'result_2', $post['result_2']);
            ConfigServer::set('baby_spleen', 'result_3', $post['result_3']);
            ConfigServer::set('baby_spleen', 'result_4', $post['result_4']);
            $this->_success('修改成功');
        }
    }

    /**
     * 睡眠设置
     */
    public function sleep()
    {
        $config = [
            'result_0_up' => ConfigServer::get('baby_sleep', 'result_0_up' ),
            'result_0_down' => ConfigServer::get('baby_sleep', 'result_0_down' ),
            'result_1_up' => ConfigServer::get('baby_sleep', 'result_1_up' ),
            'result_1_down' => ConfigServer::get('baby_sleep', 'result_1_down' ),
            'result_2_up' => ConfigServer::get('baby_sleep', 'result_2_up' ),
            'result_2_down' => ConfigServer::get('baby_sleep', 'result_2_down' ),
            'result_3_up' => ConfigServer::get('baby_sleep', 'result_3_up' ),
            'result_3_down' => ConfigServer::get('baby_sleep', 'result_3_down' ),
            'result_4_up' => ConfigServer::get('baby_sleep', 'result_4_up' ),
            'result_4_down' => ConfigServer::get('baby_sleep', 'result_4_down' ),
            'result_5_up' => ConfigServer::get('baby_sleep', 'result_5_up' ),
            'result_5_down' => ConfigServer::get('baby_sleep', 'result_5_down' ),
            'result_total_up' => ConfigServer::get('baby_sleep', 'result_total_up' ),
            'result_total_down' => ConfigServer::get('baby_sleep', 'result_total_down' ),
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }

    public function setSleep()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('baby_sleep', 'result_0_up', $post['result_0_up']);
            ConfigServer::set('baby_sleep', 'result_0_down', $post['result_0_down']);
            ConfigServer::set('baby_sleep', 'result_1_up', $post['result_1_up']);
            ConfigServer::set('baby_sleep', 'result_1_down', $post['result_1_down']);
            ConfigServer::set('baby_sleep', 'result_2_up', $post['result_2_up']);
            ConfigServer::set('baby_sleep', 'result_2_down', $post['result_2_down']);
            ConfigServer::set('baby_sleep', 'result_3_up', $post['result_3_up']);
            ConfigServer::set('baby_sleep', 'result_3_down', $post['result_3_down']);
            ConfigServer::set('baby_sleep', 'result_4_up', $post['result_4_up']);
            ConfigServer::set('baby_sleep', 'result_4_down', $post['result_4_down']);
            ConfigServer::set('baby_sleep', 'result_5_up', $post['result_5_up']);
            ConfigServer::set('baby_sleep', 'result_5_down', $post['result_5_down']);
            ConfigServer::set('baby_sleep', 'result_total_up', $post['result_total_up']);
            ConfigServer::set('baby_sleep', 'result_total_down', $post['result_total_down']);
            $this->_success('修改成功');
        }
    }
}