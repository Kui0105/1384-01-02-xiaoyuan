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
        if($re === true){
            $this->_success('添加成功');
        }
        $this->_error($re);
    }

    public function spleen_ques()
    {
        $this->_success('成功',spleen_ques());
    }
}