<?php

namespace app\api\controller;

use app\api\logic\UserNewLevelLogic;

class UserNewLevel extends ApiBase{

    /**
     * 查看下级申请列表
     */
    public function apply_list()
    {
        $this->_error('废弃');
        $page = $this->request->get('page_no', $this->page_no);
        $size = $this->request->get('page_size', $this->page_size);
        $status = $this->request->get('status',1);
        $info = UserNewLevelLogic::apply_list($this->user_id, $status, $page, $size);
        $this->_success('获取成功', $info);
    }

    /**
     * 获取申请详细
     */
    public function apply_detail()
    {
        $this->_error('废弃');
        $id = $this->request->get('id');
        $res = UserNewLevelLogic::apply_detail($id,$this->user_id);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }

    /**
     * 我的申请列表(xxx)
     */
    public function my_apply_list()
    {
        $this->_error('废弃');
        $page = $this->request->get('page_no', $this->page_no);
        $size = $this->request->get('page_size', $this->page_size);
        $status = $this->request->get('status',1);
        $info = UserNewLevelLogic::my_apply_list($this->user_id, $status, $page, $size);
        $this->_success('获取成功', $info);
    }

    /**
     * 申请审核
     */
    public function audit()
    {
        $this->_error('废弃');
        $id = $this->request->post('id');
        $status = $this->request->post('status');
        $reason = $this->request->post('reason','');
        if(!$status || !in_array($status,[1,2])){
            $this->_error('请选择审核状态');
        }
        if($status == 2 && !$reason){
            $this->_error('请填写拒绝理由');
        }
        $res = UserNewLevelLogic::audit($id,$this->user_id,$status,$reason);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功');
    }

    /**
     * 诊所-门店简介
     */
    public function hos_info()
    {
        $res = UserNewLevelLogic::hos_info($this->user_id);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }

    /**
     * 诊所-门店简介-添加/编辑
     */
    public function hos_info_edit()
    {
        $post = $this->request->post();
        $res = UserNewLevelLogic::hos_info_edit($this->user_id,$post);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }

    /**
     * 诊所-医生列表
     */
    public function hos_doctor_list()
    {
        $page = $this->request->get('page_no', $this->page_no);
        $size = $this->request->get('page_size', $this->page_size);
        $res = UserNewLevelLogic::hos_doctor_list($this->user_id,$page,$size);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }

    /**
     * 诊所-我的推荐
     */
    public function my_fans()
    {
        $get = $this->request->get();
        $page = $this->request->get('page_no', $this->page_no);
        $size = $this->request->get('page_size', $this->page_size);
        $this->_success('', UserNewLevelLogic::fans($this->user_id, $get, $page, $size));
    }

    /**
     * 诊所-编辑产品
     */
    public function product_edit()
    {
        $post = $this->request->post();
        $res = UserNewLevelLogic::product_edit($this->user_id,$post);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }

    /**
     * 诊所-添加医生
     */
    public function doctor_add()
    {
        $post = $this->request->post();
        $res = UserNewLevelLogic::doctor_add($this->user_id,$post);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }

    /**
     * 诊所-编辑医生
     */
    public function doctor_edit()
    {
        $post = $this->request->post();
        $res = UserNewLevelLogic::doctor_edit($this->user_id,$post);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }

    /**
     * 诊所-医生详细
     */
    public function doctor_info()
    {
        $id = $this->request->get('id');
        if(!$id){
            $this->_error('请选择医生');
        }
        $res = UserNewLevelLogic::doctor_info($this->user_id,$id);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }

    /**
     * 诊所-删除医生
     */
    public function doctor_del()
    {
        $id = $this->request->get('id');
        if(!$id){
            $this->_error('请选择医生');
        }
        $res = UserNewLevelLogic::doctor_del($this->user_id,$id);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }

    /**
     * 诊所-创建订单
     */
    public function create_order()
    {
        $hos_id = $this->request->post('hos_id');
        if(!$hos_id){
            $this->_error('未知诊所');
        }
        $money = $this->request->post('money');
        if(!$money){
            $this->_error('请填写金额');
        }
        if($money <= 0){
            $this->_error('金额错误');
        }
        $res = UserNewLevelLogic::create_order($this->user_id,$hos_id,$money);
        if($res === false){
            $this->_error(UserNewLevelLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }

    /**
     * 诊所-当面付订单列表
     */
    public function order_lst()
    {
        $get = $this->request->get();
        $page = $this->request->get('page_no', $this->page_no);
        $size = $this->request->get('page_size', $this->page_size);
        $this->_success('', UserNewLevelLogic::order_lst($this->user_id, $get, $page, $size));
    }
}