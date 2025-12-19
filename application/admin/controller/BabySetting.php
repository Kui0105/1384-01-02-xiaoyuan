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
            'result_1_1' => ConfigServer::get('baby_sleep', 'result_1_1' ),
            'result_1_2' => ConfigServer::get('baby_sleep', 'result_1_2' ),
            'result_1_3' => ConfigServer::get('baby_sleep', 'result_1_3' ),
            'result_2_1' => ConfigServer::get('baby_sleep', 'result_2_1' ),
            'result_2_2' => ConfigServer::get('baby_sleep', 'result_2_2' ),
            'result_2_3' => ConfigServer::get('baby_sleep', 'result_2_3' ),
            'result_3_1' => ConfigServer::get('baby_sleep', 'result_3_1' ),
            'result_3_2' => ConfigServer::get('baby_sleep', 'result_3_2' ),
            'result_3_3' => ConfigServer::get('baby_sleep', 'result_3_3' ),
            'result_4_1' => ConfigServer::get('baby_sleep', 'result_4_1' ),
            'result_4_2' => ConfigServer::get('baby_sleep', 'result_4_2' ),
            'result_5_1' => ConfigServer::get('baby_sleep', 'result_5_1' ),
            'result_5_2' => ConfigServer::get('baby_sleep', 'result_5_2' ),
            'result_6_1' => ConfigServer::get('baby_sleep', 'result_6_1' ),
            'result_6_2' => ConfigServer::get('baby_sleep', 'result_6_2' ),
            'result_7_1' => ConfigServer::get('baby_sleep', 'result_7_1' ),
            'result_7_2' => ConfigServer::get('baby_sleep', 'result_7_2' ),
            'result_8_1' => ConfigServer::get('baby_sleep', 'result_8_1' ),
            'result_8_2' => ConfigServer::get('baby_sleep', 'result_8_2' ),
            'result_total_1' => ConfigServer::get('baby_sleep', 'result_total_1' ),
            'result_total_2' => ConfigServer::get('baby_sleep', 'result_total_2' ),
            'result_total_3' => ConfigServer::get('baby_sleep', 'result_total_3' ),
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }

    public function setSleep()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('baby_sleep', 'result_1_1', $post['result_1_1']);
            ConfigServer::set('baby_sleep', 'result_1_2', $post['result_1_2']);
            ConfigServer::set('baby_sleep', 'result_1_3', $post['result_1_3']);
            ConfigServer::set('baby_sleep', 'result_2_1', $post['result_2_1']);
            ConfigServer::set('baby_sleep', 'result_2_2', $post['result_2_2']);
            ConfigServer::set('baby_sleep', 'result_2_3', $post['result_2_3']);
            ConfigServer::set('baby_sleep', 'result_3_1', $post['result_3_1']);
            ConfigServer::set('baby_sleep', 'result_3_2', $post['result_3_2']);
            ConfigServer::set('baby_sleep', 'result_3_3', $post['result_3_3']);
            ConfigServer::set('baby_sleep', 'result_4_1', $post['result_4_1']);
            ConfigServer::set('baby_sleep', 'result_4_2', $post['result_4_2']);
            ConfigServer::set('baby_sleep', 'result_5_1', $post['result_5_1']);
            ConfigServer::set('baby_sleep', 'result_5_2', $post['result_5_2']);
            ConfigServer::set('baby_sleep', 'result_6_1', $post['result_6_1']);
            ConfigServer::set('baby_sleep', 'result_6_2', $post['result_6_2']);
            ConfigServer::set('baby_sleep', 'result_7_1', $post['result_7_1']);
            ConfigServer::set('baby_sleep', 'result_7_2', $post['result_7_2']);
            ConfigServer::set('baby_sleep', 'result_8_1', $post['result_8_1']);
            ConfigServer::set('baby_sleep', 'result_8_2', $post['result_8_2']);
            ConfigServer::set('baby_sleep', 'result_total_1', $post['result_total_1']);
            ConfigServer::set('baby_sleep', 'result_total_2', $post['result_total_2']);
            ConfigServer::set('baby_sleep', 'result_total_3', $post['result_total_3']);
            $this->_success('修改成功');
        }
    }

    /**
     * 运动设置
     */
    public function sport()
    {
        $config = [
            'bmi_0' => ConfigServer::get('baby_sport', 'bmi_0' ),
            'bmi_1' => ConfigServer::get('baby_sport', 'bmi_1' ),
            'bmi_2' => ConfigServer::get('baby_sport', 'bmi_2' ),
            'bmi_3' => ConfigServer::get('baby_sport', 'bmi_3' ),
            'vital_capacity1' => ConfigServer::get('baby_sport', 'vital_capacity1' ),
            'vital_capacity2' => ConfigServer::get('baby_sport', 'vital_capacity2' ),
            'vital_capacity3' => ConfigServer::get('baby_sport', 'vital_capacity3' ),
            'vital_capacity4' => ConfigServer::get('baby_sport', 'vital_capacity4' ),
            'run_501' => ConfigServer::get('baby_sport', 'run_501' ),
            'run_502' => ConfigServer::get('baby_sport', 'run_502' ),
            'run_503' => ConfigServer::get('baby_sport', 'run_503' ),
            'run_504' => ConfigServer::get('baby_sport', 'run_504' ),
            'sit_and_reach1' => ConfigServer::get('baby_sport', 'sit_and_reach1' ),
            'sit_and_reach2' => ConfigServer::get('baby_sport', 'sit_and_reach2' ),
            'sit_and_reach3' => ConfigServer::get('baby_sport', 'sit_and_reach3' ),
            'sit_and_reach4' => ConfigServer::get('baby_sport', 'sit_and_reach4' ),
            'skip1' => ConfigServer::get('baby_sport', 'skip1' ),
            'skip2' => ConfigServer::get('baby_sport', 'skip2' ),
            'skip3' => ConfigServer::get('baby_sport', 'skip3' ),
            'skip4' => ConfigServer::get('baby_sport', 'skip4' ),
            'jump1' => ConfigServer::get('baby_sport', 'jump1' ),
            'jump2' => ConfigServer::get('baby_sport', 'jump2' ),
            'jump3' => ConfigServer::get('baby_sport', 'jump3' ),
            'jump4' => ConfigServer::get('baby_sport', 'jump4' ),
            'sit_up1' => ConfigServer::get('baby_sport', 'sit_up1' ),
            'sit_up2' => ConfigServer::get('baby_sport', 'sit_up2' ),
            'sit_up3' => ConfigServer::get('baby_sport', 'sit_up3' ),
            'sit_up4' => ConfigServer::get('baby_sport', 'sit_up4' ),
            'run_else1' => ConfigServer::get('baby_sport', 'run_else1' ),
            'run_else2' => ConfigServer::get('baby_sport', 'run_else2' ),
            'run_else3' => ConfigServer::get('baby_sport', 'run_else3' ),
            'run_else4' => ConfigServer::get('baby_sport', 'run_else4' ),
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }

    public function setSport()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('baby_sport', 'bmi_0', $post['bmi_0']);
            ConfigServer::set('baby_sport', 'bmi_1', $post['bmi_1']);
            ConfigServer::set('baby_sport', 'bmi_2', $post['bmi_2']);
            ConfigServer::set('baby_sport', 'bmi_3', $post['bmi_3']);
            ConfigServer::set('baby_sport', 'vital_capacity1', $post['vital_capacity1']);
            ConfigServer::set('baby_sport', 'vital_capacity2', $post['vital_capacity2']);
            ConfigServer::set('baby_sport', 'vital_capacity3', $post['vital_capacity3']);
            ConfigServer::set('baby_sport', 'vital_capacity4', $post['vital_capacity4']);
            ConfigServer::set('baby_sport', 'run_501', $post['run_501']);
            ConfigServer::set('baby_sport', 'run_502', $post['run_502']);
            ConfigServer::set('baby_sport', 'run_503', $post['run_503']);
            ConfigServer::set('baby_sport', 'run_504', $post['run_504']);
            ConfigServer::set('baby_sport', 'sit_and_reach1', $post['sit_and_reach1']);
            ConfigServer::set('baby_sport', 'sit_and_reach2', $post['sit_and_reach2']);
            ConfigServer::set('baby_sport', 'sit_and_reach3', $post['sit_and_reach3']);
            ConfigServer::set('baby_sport', 'sit_and_reach4', $post['sit_and_reach4']);
            ConfigServer::set('baby_sport', 'skip1', $post['skip1']);
            ConfigServer::set('baby_sport', 'skip2', $post['skip2']);
            ConfigServer::set('baby_sport', 'skip3', $post['skip3']);
            ConfigServer::set('baby_sport', 'skip4', $post['skip4']);
            ConfigServer::set('baby_sport', 'jump1', $post['jump1']);
            ConfigServer::set('baby_sport', 'jump2', $post['jump2']);
            ConfigServer::set('baby_sport', 'jump3', $post['jump3']);
            ConfigServer::set('baby_sport', 'jump4', $post['jump4']);
            ConfigServer::set('baby_sport', 'sit_up1', $post['sit_up1']);
            ConfigServer::set('baby_sport', 'sit_up2', $post['sit_up2']);
            ConfigServer::set('baby_sport', 'sit_up3', $post['sit_up3']);
            ConfigServer::set('baby_sport', 'sit_up4', $post['sit_up4']);
            ConfigServer::set('baby_sport', 'run_else1', $post['run_else1']);
            ConfigServer::set('baby_sport', 'run_else2', $post['run_else2']);
            ConfigServer::set('baby_sport', 'run_else3', $post['run_else3']);
            ConfigServer::set('baby_sport', 'run_else4', $post['run_else4']);
            $this->_success('修改成功');
        }
    }

    /**
     * 营养设置
     */
    public function nutrition()
    {
        $config = [
            'protein_lysine' => ConfigServer::get('baby_nutrition', 'protein_lysine' ),
            'vb' => ConfigServer::get('baby_nutrition', 'vb' ),
            'vd' => ConfigServer::get('baby_nutrition', 'vd' ),
            'iron' => ConfigServer::get('baby_nutrition', 'iron' ),
            'zinc' => ConfigServer::get('baby_nutrition', 'zinc' ),
            'va' => ConfigServer::get('baby_nutrition', 'va' ),
            'calcium' => ConfigServer::get('baby_nutrition', 'calcium' ),
            'dietary_fiber' => ConfigServer::get('baby_nutrition', 'dietary_fiber' ),
            'unhealthy' => ConfigServer::get('baby_nutrition', 'unhealthy' ),
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }

    public function setNutrition()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('baby_nutrition', 'protein_lysine', $post['protein_lysine']);
            ConfigServer::set('baby_nutrition', 'vb', $post['vb']);
            ConfigServer::set('baby_nutrition', 'vd', $post['vd']);
            ConfigServer::set('baby_nutrition', 'iron', $post['iron']);
            ConfigServer::set('baby_nutrition', 'zinc', $post['zinc']);
            ConfigServer::set('baby_nutrition', 'va', $post['va']);
            ConfigServer::set('baby_nutrition', 'calcium', $post['calcium']);
            ConfigServer::set('baby_nutrition', 'dietary_fiber', $post['dietary_fiber']);
            ConfigServer::set('baby_nutrition', 'unhealthy', $post['unhealthy']);
            $this->_success('修改成功');
        }
    }
}