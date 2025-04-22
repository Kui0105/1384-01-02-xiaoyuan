<?php

namespace app\api\controller;

use app\api\logic\BabyLogic;


class Baby extends ApiBase
{

    public $like_not_need_login = [];

    /**
     * 添加宝宝
     */
    public function add_baby(){
        $data = $this->request->post();
        $data['user_id'] = $this->user_id;
        $check = $this->validate($data, 'app\api\validate\Baby.add');
        if (true !== $check) {
            $this->_error($check);
        }
        $re = BabyLogic::add_baby($this->user_id, $data);
        if($re === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('成功',$re);
    }

    /**
     * 身高测评
     */
    public function height_check()
    {
        $data = $this->request->post();
        $data['user_id'] = $this->user_id;
        $check = $this->validate($data, 'app\api\validate\Baby.heightCheck');
        if (true !== $check) {
            $this->_error($check);
        }
        $re = BabyLogic::height_check_before($this->user_id, $data);
        if($re === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('成功',$re);
    }

    /**
     * 脾胃题目
     */
    public function spleen_ques()
    {
        $this->_success('成功',spleen_ques());
    }

    /**
     * 脾胃测评
     */
    public function spleen_check()
    {
        $data = $this->request->post();
        $data['user_id'] = $this->user_id;
        $re = BabyLogic::spleen_check($this->user_id, $data);
        if($re === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('成功',$re);
    }

    /**
     * 睡眠题目
     */
    public function sleep_ques()
    {
        $this->_success('成功',sleep_ques());
    }

    /**
     * 脾胃测评
     */
    public function sleep_check()
    {
        $data = $this->request->post();
        $data['user_id'] = $this->user_id;
        $re = BabyLogic::sleep_check($this->user_id, $data);
        if($re === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('成功',$re);
    }

    /**
     * 首页宝宝
     */
    public function index_baby()
    {
        $re = BabyLogic::index_baby($this->user_id);
        $this->_success('成功',$re);
    }

    /**
     * 我的宝宝
     */
    public function user_baby()
    {
        $re = BabyLogic::user_baby($this->user_id);
        $this->_success('成功',$re);
    }

    /**
     * note 我的宝宝切换列表
     */
    public function baby_list()
    {
        $get      = $this->request->get();
        $baby_list = BabyLogic::baby_list($this->user_id, $get, $this->page_no, $this->page_size);
        $this->_success('获取成功', $baby_list);
    }

    /**
     * note 设置默认宝宝
     */
    public function set_def()
    {
        $id = $this->request->post('id');
        if(!$id){
            $this->_error('请选择默认宝宝');
        }
        $result = BabyLogic::setDefaultBaby($this->user_id, $id);
        if ($result === true) {
            $this->_success('设置成功');
        }
        $this->_error($result);
    }
}