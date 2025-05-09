<?php
namespace app\admin\controller;

use app\admin\logic\DistributionLevelLogic;
use app\admin\logic\DistributionMemberLogic;
use app\admin\logic\UserNewLevelRadioLogic;
use app\admin\logic\SelffetchShopLogic;
use app\admin\logic\HosInfoLogic;
use app\common\model\DevRegion;
use app\common\server\ConfigServer;

/**
 * 分销会员
 * Class DistributionMember
 * @package app\admin\controller\distribution
 */
class DistributionMember extends AdminBase
{

    /**
     * 省代列表
     */
    public function province()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $params['level'] = 1;
            $result = UserNewLevelRadioLogic::lists($params);
            return $this->_success('', $result);
        }
        return view('', ['area_lists' => json_encode(SelffetchShopLogic::getAreaLists(), JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * @notes 添加省代
     */
    public function add_province(){
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = UserNewLevelRadioLogic::adjustProvince($post); //逻辑层处理信息
            if($result === true){
                $this->_success('操作成功');
            }
            $this->_error($result);
        }
        $this->assign('area_list',DevRegion::getLevelArea());
        return $this->fetch('adjust_province');
    }

    /**
     * @notes 移除省代
     */
    public function del_province()
    {
        $params = $this->request->post();
        $result = UserNewLevelRadioLogic::del_province($params);
        if($result) {
            return $this->_success('操作成功');
        }
        return $this->_error(UserNewLevelRadioLogic::getError());
    }

    /**
     * @notes 修改省代
     */
    public function edit_province()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $result = UserNewLevelRadioLogic::edit_province($params);
            if($result === true) {
                return $this->_success('调整成功');
            }
            return $this->_error($result);
        }
        $params = $this->request->get();
        $info = UserNewLevelRadioLogic::getInfo($params);
        return view('', [
            'info' => $info,
            'area_list' => DevRegion::getLevelArea()
        ]);
    }

    /**
     * 市代列表
     */
    public function city()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $params['level'] = 2;
            $result = UserNewLevelRadioLogic::lists($params);
            return $this->_success('', $result);
        }
        return view('', ['area_lists' => json_encode(SelffetchShopLogic::getAreaLists(), JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * @notes 添加省代
     */
    public function add_city(){
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = UserNewLevelRadioLogic::adjustCity($post); //逻辑层处理信息
            if($result === true){
                $this->_success('操作成功');
            }
            $this->_error($result);
        }
        $this->assign('area_list',DevRegion::getLevelSon(100000,2));
        return $this->fetch('');
    }

    /**
     * @notes 移除省代
     */
    public function del_city()
    {
        $params = $this->request->post();
        $result = UserNewLevelRadioLogic::del_city($params);
        if($result) {
            return $this->_success('操作成功');
        }
        return $this->_error(UserNewLevelRadioLogic::getError());
    }

    /**
     * @notes 修改省代
     */
    public function edit_city()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $result = UserNewLevelRadioLogic::edit_city($params);
            if($result === true) {
                return $this->_success('调整成功');
            }
            return $this->_error($result);
        }
        $params = $this->request->get();
        $info = UserNewLevelRadioLogic::getInfo($params);
        return view('', [
            'info' => $info,
            'area_list' => DevRegion::getLevelSon(100000,2)
        ]);
    }

    /**
     * 区代列表
     */
    public function area()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $params['level'] = 3;
            $result = UserNewLevelRadioLogic::lists($params);
            return $this->_success('', $result);
        }
        return view('', ['area_lists' => json_encode(SelffetchShopLogic::getAreaLists(), JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * @notes 添加区代
     */
    public function add_area(){
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = UserNewLevelRadioLogic::adjustArea($post); //逻辑层处理信息
            if($result === true){
                $this->_success('操作成功');
            }
            $this->_error($result);
        }
        $this->assign('area_list',DevRegion::getLevelAll());
        return $this->fetch('');
    }

    /**
     * @notes 修改区代
     */
    public function edit_area()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $result = UserNewLevelRadioLogic::edit_area($params);
            if($result === true) {
                return $this->_success('调整成功');
            }
            return $this->_error($result);
        }
        $params = $this->request->get();
        $info = UserNewLevelRadioLogic::getInfo($params);
        return view('', [
            'info' => $info,
            'area_list' => DevRegion::getLevelAll()
        ]);
    }

    /**
     * @notes 移除区代
     */
    public function del_area()
    {
        $params = $this->request->post();
        $result = UserNewLevelRadioLogic::del_area($params);
        if($result) {
            return $this->_success('操作成功');
        }
        return $this->_error(UserNewLevelRadioLogic::getError());
    }

    /**
     * 诊所列表
     */
    public function hospital()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $params['level'] = 4;
            $result = UserNewLevelRadioLogic::lists($params);
            return $this->_success('', $result);
        }
        return view('', ['area_lists' => json_encode(SelffetchShopLogic::getAreaLists(), JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * @notes 添加诊所
     */
    public function add_hos(){
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = UserNewLevelRadioLogic::adjustHos($post); //逻辑层处理信息
            if($result === true){
                $this->_success('操作成功');
            }
            $this->_error($result);
        }

        $this->assign('tx_map_key',ConfigServer::get('map','tx_map_key',''));
        $this->assign('area_lists', json_encode(SelffetchShopLogic::getAreaLists(), JSON_UNESCAPED_UNICODE));
        return $this->fetch('');
    }

    /**
     * @notes 修改诊所
     */
    public function edit_hos()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $result = UserNewLevelRadioLogic::edit_hos($params);
            if($result === true) {
                return $this->_success('调整成功');
            }
            return $this->_error($result);
        }
        $params = $this->request->get();
        $info = UserNewLevelRadioLogic::getInfo($params);
        return view('', [
            'info'          => $info,
            'tx_map_key'    => ConfigServer::get('map','tx_map_key',''),
            'area_lists'     => json_encode(SelffetchShopLogic::getAreaLists(), JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * 门店信息
     */
    public function hos_info($id){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $res = HosInfoLogic::edit($post);
            if($res === true){
                $this->_success('保存成功');
            }
            return $this->_error($res);
        }
        $detail = HosInfoLogic::getInfo($id);
        $this->assign('info',$detail);
        return $this->fetch();
    }

    /**
     * @notes 移除诊所
     */
    public function del_hos()
    {
        $params = $this->request->post();
        $result = UserNewLevelRadioLogic::del_hos($params);
        if($result) {
            return $this->_success('操作成功');
        }
        return $this->_error(UserNewLevelRadioLogic::getError());
    }

    /**
     * @notes 分销会员列表
     * @return \think\response\View
     * @author Tab
     */
    public function index()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $result = DistributionMemberLogic::lists($params);
            return $this->_success('', $result);
        }
        $levels = DistributionLevelLogic::getLevels();
        return view('', ['levels' => $levels]);
    }

    /**
     * @notes 开通分销会员
     * @return \think\response\View
     * @author Tab
     */
    public function open()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $result = DistributionMemberLogic::open($params);
            if($result) {
                return $this->_success('开通成功');
            }
            return $this->_error(DistributionMemberLogic::getError());
        }
        $levels = DistributionLevelLogic::getLevels();
        return view('', ['levels' => $levels]);
    }

    /**
     * @notes 用户列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     */
    public function userLists()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            $lists = DistributionMemberLogic::getUserLists($params);
            return $this->_success('', $lists);
        }
        return view();
    }

    /**
     * @notes 分销会员等级调整
     * @return \think\response\Json|\think\response\View
     * @author Tab
     */
    public function adjust()
    {
        if($this->request->isPost()) {
            $params = $this->request->post();
            $result = DistributionMemberLogic::adjust($params);
            if($result) {
                return $this->_success('调整成功');
            }
            return $this->_error(DistributionMemberLogic::getError());
        }
        $params = $this->request->get();
        $user = DistributionMemberLogic::getUser($params);
        $levels = DistributionLevelLogic::getLevels();
        return view('', [
            'user' => $user,
            'levels' => $levels
        ]);
    }

    /**
     * @notes 冻结资格/恢复资格
     * @return \think\response\Json
     * @author Tab
     */
    public function isFreeze()
    {
        $params = $this->request->post();
        $result = DistributionMemberLogic::isFreeze($params);
        if($result) {
            return $this->_success('操作成功');
        }
        return $this->_error(DistributionMemberLogic::getError());
    }
}