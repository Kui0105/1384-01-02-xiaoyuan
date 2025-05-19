<?php

namespace app\api\logic;

use app\common\logic\LogicBase;
use app\common\model\DevRegion;
use app\common\model\UserLevelApply;
use app\common\model\UserNewLevel;
use app\common\model\User;
use think\Db;

class UserNewLevelLogic extends LogicBase
{

    public static function getFather($user_id){
        $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area')->where('id',$user_id)->findOrEmpty()->toArray();
        if(empty($user_data)){
            return [];
        }
        $pids = [];
        switch ($user_data['new_level']){
            case 1:
                $pids = $user_data['new_level_province'];
                break;
            case 2:
                $pids = $user_data['new_level_city'];
                break;
            case 3:
                $pids = $user_data['new_level_area'];
                break;
        }
        $user_data['pids'] = $pids;
        return $user_data;
    }

    /**
     * 查看下级申请列表
     */
    public static function apply_list($user_id, $status, $page, $size)
    {
        // $level_data = UserNewLevel::where('user_id',$user_id)->findOrEmpty()->toArray();
        $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area')->where('id',$user_id)->findOrEmpty()->toArray();
        if(empty($user_data) || $user_data['new_level'] == 4 || !$user_data['new_level']){
            return [
                'list' => [],
                'page' => $page,
                'size' => $size,
                'count' => 0,
                'more' => is_more(0, $page, $size)
            ];
        }
        $pids = [];
        switch ($user_data['new_level']){
            case 1:
                $pids = $user_data['new_level_province'];
                break;
            case 2:
                $pids = $user_data['new_level_city'];
                break;
            case 3:
                $pids = $user_data['new_level_area'];
                break;
        }
        $status = $status == 2?2:1;
        $where = [
            ['c.pid', 'in', (int)$pids]
        ];

        $where[] = ['c.status', '=', (int)$status];

        $model = new UserLevelApply();
        $count = $model->alias('c')->leftJoin('user u','u.id = c.user_id')->where($where)->count();
        $lists = $model->alias('c')
            ->leftJoin('user u','u.id = c.user_id')
            ->field('c.id,c.level,c.create_time,u.nickname,c.status')
            ->where($where)
            ->order('c.id', 'desc')
            ->page($page, $size)
            ->select();

        $level_arr = [
            2 => '市',
            3 => '区县',
            4 => '诊所',
        ];
        foreach ($lists as &$item) {
           $item['create_time'] = getCTime($item['create_time']);
           $item['level_str'] = $level_arr[$item['level']];
        }

        return [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
    }

    /**
     * 获取申请详细
     */
    public static function apply_detail($id,$user_id)
    {
        // $level_data = UserNewLevel::where('user_id',$user_id)->findOrEmpty()->toArray();
        $user_data = self::getFather($user_id);
        if(empty($user_data) || $user_data['new_level'] == 4  || !$user_data['new_level']){
            self::$error = '暂无权限';
            return false;
        }

        $where = [
            ['c.pid', 'in', (int)$user_data['pids']],
            ['c.id', '=', $id]
        ];

        $model = new UserLevelApply();
        $detail = $model->alias('c')
            ->leftJoin('user u','u.id = c.user_id')
            ->field('c.id,c.level,c.create_time,u.nickname,c.status,u.avatar,c.name,c.mobile,c.self_id,c.address,c.latitude,c.longitude,c.remark,c.status,c.reason,c.audit_time,c.pid')
            ->where($where)
            ->findOrEmpty()->toArray();
        if(empty($detail)){
            self::$error = '记录不存在';
            return false;
        }
        $level_arr = [
            2 => '市',
            3 => '区县',
            4 => '诊所',
        ];
        $detail['create_time'] = getCTime($detail['create_time']);
        $detail['avatar'] = addImgDomain($detail['avatar']);
        $detail['audit_time'] = $detail['audit_time']?getCTime($detail['audit_time']):'';
        $detail['level_str'] = $level_arr[$detail['level']];
        $detail['pid_str'] = DevRegion::getArea($detail['pid']);
        $detail['self_id_str'] = DevRegion::getArea($detail['self_id']);
        return $detail;
    }

    /**
     * 我的申请列表(xxx)
     */
    public static function my_apply_list($user_id, $status, $page, $size)
    {
        // $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area')->where('id',$user_id)->findOrEmpty()->toArray();
        // if(empty($user_data) || $user_data['new_level'] == 1 || !$user_data['new_level']){
        //     return [
        //         'list' => [],
        //         'page' => $page,
        //         'size' => $size,
        //         'count' => 0,
        //         'more' => is_more(0, $page, $size)
        //     ];
        // }
        // $pids = [];
        // switch ($user_data['new_level']){
        //     case 2:
        //         $pids = $user_data['new_level_province'];
        //         break;
        //     case 3:
        //         $pids = $user_data['new_level_city'];
        //         break;
        //     case 4:
        //         $pids = $user_data['new_level_area'];
        //         break;
        // }
        // $status = $status == 2?2:1;
        // $where = [
        //     ['c.pid', 'in', (int)$pids]
        // ];
        //
        // $where[] = ['c.status', '=', (int)$status];
        //
        // $model = new UserLevelApply();
        // $count = $model->alias('c')->leftJoin('user u','u.id = c.user_id')->where($where)->count();
        // $lists = $model->alias('c')
        //     ->leftJoin('user u','u.id = c.user_id')
        //     ->field('c.id,c.level,c.create_time,u.nickname,c.status')
        //     ->where($where)
        //     ->order('c.id', 'desc')
        //     ->page($page, $size)
        //     ->select();
        //
        // $level_arr = [
        //     2 => '市',
        //     3 => '区县',
        //     4 => '诊所',
        // ];
        // foreach ($lists as &$item) {
        //     $item['create_time'] = getCTime($item['create_time']);
        //     $item['level_str'] = $level_arr[$item['level']];
        // }
        //
        // return [
        //     'list' => $lists,
        //     'page' => $page,
        //     'size' => $size,
        //     'count' => $count,
        //     'more' => is_more($count, $page, $size)
        // ];
    }

    /**
     * 申请审核
     */
    public static function audit($id,$user_id,$status,$reason)
    {
        // $level_data = UserNewLevel::where('user_id',$user_id)->findOrEmpty()->toArray();
        $user_data = self::getFather($user_id);
        if(empty($user_data) || $user_data['new_level'] == 4  || !$user_data['new_level']){
            self::$error = '暂无权限';
            return false;
        }
        $where = [
            ['c.pid', 'in', (int)$user_data['pids']],
            ['c.id', '=', $id]
        ];
        $model = new UserLevelApply();
        $detail = $model->alias('c')
            ->leftJoin('user u','u.id = c.user_id')
            ->field('c.id,c.level,c.create_time,u.nickname,c.status,u.avatar,c.name,c.mobile,c.self_id,c.address,c.latitude,c.longitude,c.remark,c.status,c.reason,c.audit_time,c.pid,c.user_id')
            ->where($where)
            ->findOrEmpty()->toArray();
        if(empty($detail)){
            self::$error = '记录不存在';
            return false;
        }
        if($detail['status'] !=1){
            self::$error = '不是可审核状态';
            return false;
        }

        Db::startTrans();
        try {
            $time = time();
            if($status == 1){
                $model->where('id',$id)->update([
                    'status'        => 2,
                    'reason'        => $reason,
                    'audit_time'    => $time,
                ]);
                $son_user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area,new_level_hos')->where('id',$detail['user_id'])->findOrEmpty()->toArray();
                if($son_user_data['new_level'] && $son_user_data['new_level'] != $detail['level']){
                    self::$error = '不可多个级别';
                    return false;
                }

                $user_value = '';
                if($son_user_data['new_level']){
                    switch ($son_user_data['new_level']){
                        case 2:
                            $user_key = 'new_level_city';
                            break;
                        case 3:
                            $user_key = 'new_level_area';
                            break;
                        case 4:
                            self::$error = '已是诊所';
                            return false;
                            // $user_key = 'new_level_hos';
                            break;
                    }
                    $user_value = $son_user_data[$user_key];
                }

                $hos_id = UserNewLevel::insertGetId([
                    'user_id'       => $detail['user_id'],
                    'level'         => $detail['level'],
                    'pid'           => $detail['pid'],
                    'self_id'       => $detail['self_id'],
                    'name'          => $detail['name'],
                    'mobile'        => $detail['mobile'],
                    'latitude'      => $detail['latitude'],
                    'longitude'     => $detail['longitude'],
                    'address'       => $detail['address'],
                    'create_time'   => $time
                ]);

                $hos_p_area_id = 0;
                switch ($detail['level']){
                    case 2:
                        if($user_value){
                            $user_value = $user_value.','.$detail['self_id'];
                        }else{
                            $user_value = $detail['self_id'];
                        }
                        $update_user_key = 'new_level_city';
                        break;
                    case 3:
                        if($user_value){
                            $user_value = $user_value.','.$detail['self_id'];
                        }else{
                            $user_value = $detail['self_id'];
                        }
                        $update_user_key = 'new_level_area';
                        break;
                    case 4:
                        if($user_value){
                            $user_value = $user_value.','.$hos_id;
                        }else{
                            $user_value = $hos_id;
                        }
                        $update_user_key = 'new_level_hos';
                        $hos_p_area_id = $detail['pid'];
                        break;
                    default:
                        self::$error = '系统错误';
                        return false;
                }
                $user_update = [
                    $update_user_key    => $user_value,
                    'new_level'         => $detail['level'],
                    'hos_p_area_id'     => $hos_p_area_id
                ];

                User::where('id',$son_user_data['id'])->update($user_update);
                if($detail['level'] == 4){
                    $model->where(['level'=>$detail['level'],'pid'=>$detail['pid'],'self_id'=>$detail['self_id'],'status'=>1,'user_id'=>$son_user_data['id']])->where('id','<>',$detail['id'])->update([
                        'status' => 3,
                        'reason' => '自动驳回其他诊所申请',
                        'audit_time' => $time
                    ]);
                }else{
                    $model->where(['level'=>$detail['level'],'pid'=>$detail['pid'],'self_id'=>$detail['self_id'],'status'=>1])->where('id','<>',$detail['id'])->update([
                        'status' => 3,
                        'reason' => '自动驳回同地区申请',
                        'audit_time' => $time
                    ]);
                }

            }else{
                $model->where('id',$id)->update([
                    'status'        => 3,
                    'reason'        => $reason,
                    'audit_time'    => $time,
                ]);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 诊所-门店简介
     */
    public static function hos_info($user_id)
    {
        $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area,new_level_hos')->where('id',$user_id)->findOrEmpty()->toArray();
        if($user_data['new_level'] != 4){
            self::$error = '等级错误';
            return false;
        }
        $hos_info = Db::name('hos_info')->field('id,name,imgs,intro')->where('user_id',$user_id)->findOrEmpty();
        if($hos_info){
            $hos_info['imgs'] = addImgDomain($hos_info['imgs']);
        }else{
            $hos_info = '';
        }
        return $hos_info;
    }

    /**
     * 诊所-门店简介-添加/编辑
     */
    public static function hos_info_edit($user_id,$data)
    {
        if(empty($data['name'])){
            self::$error = '请输入名称';
            return false;
        }
        if(empty($data['imgs'])){
            self::$error = '请上传图片';
            return false;
        }
        if(empty($data['intro'])){
            self::$error = '请输入简介';
            return false;
        }
        $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area,new_level_hos')->where('id',$user_id)->findOrEmpty()->toArray();
        if($user_data['new_level'] != 4){
            self::$error = '等级错误';
            return false;
        }
        $cache = cache('hos_info_edit_'.$user_id);
        if($cache){
            self::$error = '操作频繁';
            return  false;
        }
        cache('hos_info_edit_'.$user_id,1,2);
        $id = Db::name('hos_info')->where('user_id',$user_id)->value('id');
        $time = time();
        if($id){
            Db::name('hos_info')->where('id',$id)->update([
                'name'  => $data['name'],
                'imgs'  => clearDomain($data['imgs']),
                'intro' => $data['intro'],
                'update_time'   => $time
            ]);
        }else{
            Db::name('hos_info')->insert([
                'user_id'   => $user_id,
                'hos_id'    => $user_data['new_level_hos'],
                'name'      => $data['name'],
                'imgs'      => clearDomain($data['imgs']),
                'intro'     => $data['intro'],
                'create_time'   => $time,
                'update_time'   => $time
            ]);
        }
        return true;
    }

    /**
     * 诊所-医生列表
     */
    public static function hos_doctor_list($user_id,$page, $size)
    {
        $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area,new_level_hos')->where('id',$user_id)->findOrEmpty()->toArray();
        if($user_data['new_level'] != 4){
            self::$error = '等级错误';
            return false;
        }
        $doctor_lst = Db::name('hos_doctor')
            ->field('id,name,imgs,intro')
            ->where(['user_id'=>$user_id,'del'=>0])
            ->order('id', 'desc')
            ->page($page, $size)
            ->select();
        $count = Db::name('hos_doctor')->where('user_id',$user_id)->count();
        if(!empty($doctor_lst)){
            foreach ($doctor_lst as &$v){
                $v['imgs'] =  addImgDomain($v['imgs']);
            }
        }
        return [
            'list' => $doctor_lst,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return ;
    }

    /**
     * 诊所-添加医生
     */
    public static function doctor_add($user_id,$data)
    {
        if(empty($data['name'])){
            self::$error = '请输入名称';
            return false;
        }
        if(empty($data['imgs'])){
            self::$error = '请上传图片';
            return false;
        }
        if(empty($data['intro'])){
            self::$error = '请输入简介';
            return false;
        }
        $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area,new_level_hos')->where('id',$user_id)->findOrEmpty()->toArray();
        if($user_data['new_level'] != 4){
            self::$error = '等级错误';
            return false;
        }
        $cache = cache('doctor_add_'.$user_id);
        if($cache){
            self::$error = '操作频繁';
            return  false;
        }
        cache('doctor_add_'.$user_id,1,2);
        $time = time();
        Db::name('hos_doctor')->insert([
            'user_id'   => $user_id,
            'hos_id'    => $user_data['new_level_hos'],
            'name'      => $data['name'],
            'imgs'      => clearDomain($data['imgs']),
            'intro'     => $data['intro'],
            'create_time'   => $time,
            'update_time'   => $time
        ]);
        return true;
    }

    /**
     * 诊所-编辑医生
     */
    public static function doctor_edit($user_id,$data)
    {
        if(empty($data['id'])){
            self::$error = '请选择医生';
            return false;
        }
        if(empty($data['name'])){
            self::$error = '请输入名称';
            return false;
        }
        if(empty($data['imgs'])){
            self::$error = '请上传图片';
            return false;
        }
        if(empty($data['intro'])){
            self::$error = '请输入简介';
            return false;
        }
        $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area,new_level_hos')->where('id',$user_id)->findOrEmpty()->toArray();
        if($user_data['new_level'] != 4){
            self::$error = '等级错误';
            return false;
        }
        $time = time();
        $doc = Db::name('hos_doctor')->where('id',$data['id'])->where('user_id',$user_id)->find();
        if(!$doc){
            self::$error = '医生错误';
            return false;
        }
        Db::name('hos_doctor')->where('id',$doc['id'])->update([
            'name'  => $data['name'],
            'imgs'  => clearDomain($data['imgs']),
            'intro' => $data['intro'],
            'update_time'   => $time
        ]);
        return true;
    }

    /**
     * 诊所-医生详细
     */
    public static function doctor_info($user_id,$id)
    {
        $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area,new_level_hos')->where('id',$user_id)->findOrEmpty()->toArray();
        if($user_data['new_level'] != 4){
            self::$error = '等级错误';
            return false;
        }
        $hos_info = Db::name('hos_doctor')->field('id,name,imgs,intro')->where(['id'=>$id,'user_id'=>$user_id,'del'=>0])->findOrEmpty();
        if($hos_info){
            $hos_info['imgs'] = addImgDomain($hos_info['imgs']);
        }
        return $hos_info;
    }

    /**
     * 诊所-删除医生
     */
    public static function doctor_del($user_id,$id)
    {
        $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area,new_level_hos')->where('id',$user_id)->findOrEmpty()->toArray();
        if($user_data['new_level'] != 4){
            self::$error = '等级错误';
            return false;
        }
        $hos_info = Db::name('hos_doctor')->where(['id'=>$id,'user_id'=>$user_id,'del'=>0])->count();
        if(!$hos_info){
            self::$error = '未找到该医生';
            return false;
        }
        $time = time();
        Db::name('hos_doctor')->where('id',$id)->update(['del'=>$time]);
        return true;
    }

    /**
     * 诊所-我的推荐
     */
    public static function fans($user_id, $get, $page, $size)
    {
        $where = [];
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $where[] = ['nickname|mobile|hp.name', 'like', '%' . $get['keyword'] . '%'];
        }

        $where[] = ['first_leader', '=', $user_id];

        $field = 'u.id, avatar, nickname, mobile, u.create_time,hp.name,hp.end_time';

        $count = Db::name('user u')
            ->field($field)
            ->leftJoin('hos_product hp', 'hp.son_id = u.id and hp.del = 0')
            ->where($where)
            ->count();

        $lists = Db::name('user u')
            ->field($field)
            ->leftJoin('hos_product hp', 'hp.son_id = u.id and hp.del = 0')
            ->where($where)
            ->page($page, $size)
            ->order('id desc')
            ->select();

        $time = time();
        foreach ($lists as &$item) {
            $item['avatar'] = addImgDomain($item['avatar']);
            $item['name'] = $item['name']?:'';
            $item['btn_status'] = true;
            if($item['end_time'] && ($item['end_time'] > $time) ){
                $item['btn_status'] = false;
            }
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['end_time'] = $item['end_time']?date('Y-m-d', $item['end_time']):'';

        }

        $data = [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $data;
    }

    /**
     * 诊所-编辑产品
     */
    public static function product_edit($user_id,$data)
    {
        $user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area,new_level_hos')->where('id',$user_id)->findOrEmpty()->toArray();
        if($user_data['new_level'] != 4){
            self::$error = '不是诊所';
            return false;
        }
        if(empty($data['id'])){
            self::$error = '请选择用户';
            return false;
        }
        if(empty($data['name'])){
            self::$error = '请输入产品名称';
            return false;
        }
        if(empty($data['end_time'])){
            self::$error = '请选择到期时间';
            return false;
        }
        $hos_id = $user_data['new_level_hos'];
        $son_parent_id = User::where('id',$data['id'])->value('first_leader');
        if($son_parent_id != $user_id){
            self::$error = '无操作权限';
            return false;
        }
        $if_product_data = Db::name('hos_product')->where(['del'=>0,'son_id'=>$data['id']])->find();
        $time = time();
        if($if_product_data){
            Db::name('hos_product')->where('id',$if_product_data['id'])->update([
                'name'      => $data['name'],
                'end_time'  => strtotime($data['end_time']),
                'status'    => 1,
                'update_time'   => $time,
            ]);
        }else{
            Db::name('hos_product')->insert([
                'user_id'   => $user_id,
                'hos_id'    => $hos_id,
                'son_id'    => $data['id'],
                'name'      => $data['name'],
                'end_time'  => strtotime($data['end_time']),
                'create_time'   => $time
            ]);
        }
        return true;
    }

    /**
     * 诊所-创建订单
     * $hos_id 诊所id
     * $money 支付金额
     */
    public static function create_order($user_id,$hos_id,$money)
    {
        $user_parent_id = User::where('id',$user_id)->value('first_leader');
        if(!$user_parent_id){
            self::$error = '未绑定诊所';
            return false;
        }
        $level_data = Db::name('user_new_level')->where('id',$hos_id)->find();
        if(!$level_data){
            self::$error = '诊所不存在';
            return false;
        }
        if($user_parent_id != $level_data['user_id']){
            self::$error = '非绑定诊所';
            return false;
        }

        $order_data = [
            'order_sn'          => createSn('hos_order', 'order_sn', '', 4),
            'user_id'           => $user_id,
            'hos_id'            => $hos_id,
            'hos_user_id'       => $level_data['user_id'],
            'order_amount'      => $money,
            'pay_status'        => 0,
            'pay_time'          => 0,
            'wechat_mini_express_sync'          => '',
            'wechat_mini_express_sync_time'     => '',
            'transaction_id'    => '',
            'create_time'       => time(),
        ];
        $order_id = Db::name('hos_order')->insertGetId($order_data);
        return ['order_id' => $order_id, 'type' => 'hos_order'];
    }

    /**
     * 诊所-当面付订单列表
     */
    public static function order_lst($user_id, $get, $page, $size)
    {
        $where = [];

        $hos_id = Db::name('user_new_level')->where(['user_id'=>$user_id,'level'=>4])->value('id');
        $where[] = ['o.hos_id', '=', $hos_id];
        // $where[] = ['o.hos_user_id', '=', $user_id];

        $field = 'u.avatar, nickname, mobile, u.create_time,o.order_sn,o.id,o.order_amount,o.pay_status,o.pay_time';

        $count = Db::name('hos_order o')
            ->field($field)
            ->leftJoin('user u', 'u.id = o.user_id')
            ->where($where)
            ->count();

        $lists = Db::name('hos_order o')
            ->field($field)
            ->leftJoin('user u', 'u.id = o.user_id')
            ->where($where)
            ->page($page, $size)
            ->order('o.id desc')
            ->select();

        foreach ($lists as &$item) {
            $item['avatar'] = addImgDomain($item['avatar']);
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['pay_time'] = $item['pay_time']?date('Y-m-d', $item['pay_time']):'';
            $item['pay_str'] = $item['pay_status']?'已支付':'未支付';
        }

        $data = [
            'list' => $lists,
            'page' => $page,
            'size' => $size,
            'count' => $count,
            'more' => is_more($count, $page, $size)
        ];
        return $data;
    }
}