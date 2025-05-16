<?php



namespace app\api\controller;


use app\api\logic\DistributionLogic;
use app\common\model\User;

class Distribution extends ApiBase
{
    public $like_not_need_login = ['fixAncestorRelation'];

    /**
     * 推广下级
     */
    public function father()
    {
        $hos_id = $this->request->post('hos_id');
        $user_id = $this->request->post('user_id');
        $result = DistributionLogic::father($hos_id,$user_id, $this->user_id);
        if ($result !== true) {
            $this->_error($result, [], 0, 0);
        }
        $this->_success();
    }

    /**
     * 填写邀请码
     */
    public function code()
    {
        $code = $this->request->post('code');
        $data = [
            'user_id' => $this->user_id,
            'code' => $code,
        ];
        $result = $this->validate($data, 'app\api\validate\DistributionCode');
        if ($result !== true) {
            $this->_error($result, [], 0, 0);
        }
        $result = DistributionLogic::code($code, $this->user_id);
        if ($result !== true) {
            $this->_error($result, [], 0, 0);
        }
        $this->_success();
    }

    /**
     * 分销会员申请
     */
    public function apple()
    {
        $post = $this->request->post();
        $post['user_id'] = $this->user_id;
        $result = $this->validate($post, 'app\api\validate\DistributionApply');
        if ($result !== true) {
            $this->_error($result);
        }
        $result = DistributionLogic::apple($post,$this->user_id);
        if ($result !== true) {
            $this->_error($result);
        }
        $this->_success();
    }

    /**
     * 最新的分销会员申请详情
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function appleDetail()
    {
        $this->_success('',DistributionLogic::appleDetail($this->user_id));
    }


    /**
     * User:  mjf
     * Desc: 我的分销上级
     */
    public function myLeader()
    {
        $this->_success('', DistributionLogic::myLeader($this->user_id));
    }


    /**
     * 分销推广主页信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $res = DistributionLogic::index($this->user_id);
        if($res === false){
            $this->_error('暂无等级');
        }
        $this->_success('',$res);
    }



    /**
     * 分销订单
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function order()
    {
        $get = $this->request->get();
        $this->_success('', DistributionLogic::order($this->user_id, $get, $this->page_no, $this->page_size));
    }


    /**
     * 月度账单
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function monthBill()
    {
        $this->_success('', DistributionLogic::getMonthBill($this->user_id, $this->page_no, $this->page_size));
    }

    /**
     * 月度账单明细
     */
    public function monthDetail()
    {
        $get = $this->request->get();
        $this->_success('', DistributionLogic::getMonth($get, $this->user_id, $this->page_no, $this->page_size));
    }


    /**
     * Notes: 分销会员页面判断
     * @author 段誉(2021/2/1 18:45)
     */
    public function check()
    {
        $distribution = \app\common\model\Distribution::where('user_id', $this->user_id)->findOrEmpty()->toArray();
        if (!empty($distribution) && $distribution['is_distribution'] == 1) {
            $this->_success('', '', 10001);//已是分销会员
        } else {
            $this->_success('', '',20001);//未是分销会员
        }
    }

    /**
     * 修复旧的关系链
     */
    public function fixAncestorRelation()
    {
        $result = DistributionLogic::fixAncestorRelation();
        if ($result['flag']) {
            $this->_success($result['msg']);
        }
        $this->_error($result['msg']);
    }


    /**
     * @notes 获取背景海报
     * @return Json
     * @author cjhao
     * @date 2021/11/29 11:35
     */
    public function getPoster()
    {
        $result = DistributionLogic::getPoster();
        $this->_success('',$result);
    }

    public function hos_info()
    {
        $hos_id = $this->request->get('hos_id');
        if(!$hos_id){
            $this->_error('缺少必要参数');
        }
        $res = DistributionLogic::hos_info($hos_id,$this->user_id);
        if($res === false){
            $this->_error(DistributionLogic::getError() ?? '失败');
        }
        return $this->_success('成功',$res);
    }
}