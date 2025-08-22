<?php

namespace app\api\controller;

use app\api\logic\NutritionLogic;
use app\api\logic\BabyLogic;
use think\facade\Cache;


class Baby extends ApiBase
{

    public $like_not_need_login = ['check_result','set_tmp','get_tmp','all_check_result'];

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
        $this->_success('添加宝贝成功',$re);
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
     * 骨龄测评
     */
    public function bone_check()
    {
        $pdf = $this->request->post('pdf');
        if(!$pdf){
            $this->_error('请上传PDF');
        }
        $re = BabyLogic::bone_check($this->user_id, $pdf);
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

    /**
     * note 编辑宝宝详情
     */
    public function baby_info()
    {
        $baby = BabyLogic::baby_info($this->user_id);
        if ($baby) {
            $this->_success('获取成功', $baby);
        }
        $this->_error(BabyLogic::getError() ?? '失败');
    }

    /**
     * note 编辑宝宝
     */
    public function edit_baby()
    {
        $data = $this->request->post();
        $data['user_id'] = $this->user_id;
        $check = $this->validate($data, 'app\api\validate\Baby.edit');
        if (true !== $check) {
            $this->_error($check);
        }
        $re = BabyLogic::edit_baby($this->user_id, $data);
        if($re === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('成功',$re);
    }

    /**
     * note 历史测评记录
     */
    public function history_check_list()
    {
        $get      = $this->request->get();
        $baby_list = BabyLogic::history_check_list($this->user_id, $get, $this->page_no, $this->page_size);
        if($baby_list === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('获取成功', $baby_list);
    }

    /**
     * note 测评报告
     */
    public function check_result()
    {
        $id = $this->request->get('id');
        if(!$id){
            $this->_error('缺少必要参数');
        }
        // $data['user_id'] = $this->user_id;
        $re = BabyLogic::check_result($this->user_id,$id);//$this->user_id,
        if($re === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('成功',$re);
    }

    /**
     * note 身高档案-记录列表
     */
    public function height_log_list()
    {
        $get      = $this->request->get();
        $baby_list = BabyLogic::height_log_list($this->user_id, $get, $this->page_no, $this->page_size);
        if($baby_list === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('获取成功', $baby_list);
    }

    public function height_log_del()
    {
        $id = $this->request->get('id');
        if(!$id){
            $this->_error('缺少必要参数');
        }
        $re = BabyLogic::height_log_del($this->user_id,$id);//$this->user_id,
        if($re === true){
            $this->_success('成功');
        }
        $this->_error($re);
    }

    /**
     * note 身高档案-身高曲线图
     */
    public function height_log_near()
    {
        $get      = $this->request->get();
        $baby_list = BabyLogic::height_log_near($this->user_id, $get);
        if($baby_list === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('获取成功', $baby_list);
    }

    /**
     * note 运动测评-年级下拉框
     */
    public function sport_grade()
    {
        $this->_success('获取成功', sport_grade_arr());
    }

    /**
     * note 运动测评-年级-测评下拉框
     */
    public function sport_check_select()
    {
        $id = $this->request->get('id');
        if(!$id){
            $this->_error('缺少必要参数');
        }
        $data['user_id'] = $this->user_id;
        $re = BabyLogic::sport_check_select($this->user_id, $id);
        if($re === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('成功',$re);
    }

    /**
     * note 运动测评
     */
    public function sport_check()
    {
        $data = $this->request->post();
        $data['user_id'] = $this->user_id;
        $data['bmi'] = round($data['weight'] / ($data['height'] / 100) ** 2,2);
        $re = BabyLogic::sport_check($this->user_id, $data);
        if($re === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('成功',$re);
    }

    /**
     * note 综合测评结果
     */
    public function all_check_result()
    {
        // $data = $this->request->post();
        // $data['user_id'] = $this->user_id;
        $sn = $this->request->get('sn');
        $re = BabyLogic::all_check_result($sn);//$this->user_id, $data
        if($re === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('成功',$re);
    }

    /**
     * note 前端中转
     */
    public function set_tmp()
    {
        $key = $this->request->post('key');
        $value = $this->request->post('value');
        Cache::set($key, $value,60);
        $res = Cache::get($key);
        $this->_success('成功',$res);
    }

    /**
     * note 前端中转
     */
    public function get_tmp()
    {
        $key = $this->request->get('key');
        $res = Cache::get($key);
        $this->_success('成功',$res);
    }

    /**
     * note 睡眠测评题目
     */
    public function nutrition_ques()
    {
        $this->_success('成功',NutritionLogic::nutrition_ques());
    }

    /**
     * 睡眠测评
     */
    public function nutrition_check()
    {
        $data = $this->request->post();
        $re = BabyLogic::nutrition_check($this->user_id, $data);
        if($re === false){
            $this->_error(BabyLogic::getError() ?? '失败');
        }
        $this->_success('成功',$re);
    }
}