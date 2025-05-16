<?php
namespace app\admin\logic;

use app\common\logic\LogicBase;
use app\common\model\DevRegion;
use app\common\model\NoticeSetting;
use app\common\model\User;
use app\common\model\UserLevelApply;
use app\common\model\UserNewLevel;
use app\common\server\AreaServer;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\Db;
use think\facade\Hook;

class UserLevelApplyLogic extends LogicBase
{
    public static function lists($get)
    {
        try {
            $where[] = ['a.status', '=', $get['status'] ?: 1];
            if (!empty($get['keyword']) and $get['keyword']) {
                switch ($get['keyword_type']) {
                    case 'name':
                        $where[] = ['a.name|a.mobile', 'like', '%'.$get['keyword'].'%'];
                        break;
                    case 'nickname':
                        $where[] = ['u.nickname', 'like', '%'.$get['keyword'].'%'];
                        break;
                    case 'mobile':
                        $where[] = ['u.mobile', '=', $get['keyword']];
                        break;
                }
            }

            if (!empty($get['level']) && $get['level']) {
                $where[] = ['a.level', '=', $get['level']];
                switch ($get['level']){
                    case 1:
                        if (!empty($get['province']) && $get['province']) {
                            $where[] = ['', 'exp', Db::raw("FIND_IN_SET({$get['province']}, a.self_id)")];
                        }
                        break;
                    case 2:
                        if (!empty($get['city']) && $get['city']) {
                            $where[] = ['', 'exp', Db::raw("FIND_IN_SET({$get['city']}, a.self_id)")];
                        }
                        break;
                    case 3:
                        if (!empty($get['district']) && $get['district']) {
                            $where[] = ['', 'exp', Db::raw("FIND_IN_SET({$get['district']}, a.self_id)")];
                        }
                        break;
                    case 4:
                        if (!empty($get['district']) && $get['district']) {
                            $where[] = ['', 'exp', Db::raw("FIND_IN_SET({$get['district']}, a.pid)")];
                        }
                        break;
                }
            }

            $model = new UserLevelApply();
            $lists = $model->field('a.*,u.nickname,u.avatar,u.mobile as user_mobile')->alias('a')
                ->where($where)
                ->leftJoin('user u', 'u.id = a.user_id')
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])->toArray();

            foreach ($lists['data'] as &$item) {
                $item['avatar'] = addImgDomain($item['avatar']);
                $item['status_name'] = $model::getApplyStatus($item['status']);
                $item['level_name'] = $model::getLevelName($item['level']);
                $item['level_area'] = $model::getLevelArea($item['level'],$item['self_id'],$item['pid']);
                $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
                $item['audit_time'] = $item['audit_time']?date('Y-m-d H:i:s',$item['audit_time']):'';
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (\Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    public static function detail($id)
    {
        $model = new UserLevelApply();
        $detail = $model->field('a.*,u.nickname,u.avatar,u.mobile as user_mobile')->alias('a')
            ->leftJoin('user u', 'u.id = a.user_id')
            ->where('a.id',$id)
            ->findOrEmpty()
            ->toArray();

        $detail['avatar'] = addImgDomain($detail['avatar']);
        $detail['status_name'] = $model::getApplyStatus($detail['status']);
        $detail['level_name'] = $model::getLevelName($detail['level']);
        $detail['level_area'] = $model::getLevelArea($detail['level'],$detail['self_id'],$detail['pid']);
        $detail['create_time'] = date('Y-m-d H:i:s',$detail['create_time']);
        $detail['audit_time'] = $detail['audit_time']?date('Y-m-d H:i:s',$detail['audit_time']):'';
        return $detail;
    }

    public static function radio_data($id)
    {
        $radio_data = [];
        $detail = UserLevelApply::where('id',$id)->findOrEmpty()->toArray();
        switch ($detail['level']){
            case 1:
                $point = ConfigServer::get('distribution', 'province_point', 0);
                $radio_data[] = [
                    'id'    => $detail['self_id'],
                    'name'  => UserLevelApply::getLevelArea(1,$detail['self_id'],0,true).'(省代)',
                    'point' => $point
                ];
                break;
            case 2:
                $point = ConfigServer::get('distribution', 'city_point', 0);
                $id_arr = explode(',',$detail['self_id']);
                foreach ($id_arr as $v){
                    $radio_data[] = [
                        'id'    => $v,
                        'name'  => UserLevelApply::getLevelArea(2,$v,0,true).'(市代)',
                        'point' => $point
                    ];
                }
                break;
            case 3:
                $point = ConfigServer::get('distribution', 'area_point', 0);
                $id_arr = explode(',',$detail['self_id']);
                foreach ($id_arr as $v){
                    $radio_data[] = [
                        'id'    => $v,
                        'name'  => UserLevelApply::getLevelArea(3,$v,0,true).'(区代)',
                        'point' => $point
                    ];
                }
                break;
            case 4:
                $point = ConfigServer::get('distribution', 'hospital_point', 0);
                $radio_data[] = [
                    'id'    => $detail['pid'],
                    'name'  => UserLevelApply::getLevelArea(4,$detail['self_id'],$detail['pid'],true).'(诊所)',
                    'point' => $point
                ];
                break;
        }
        return $radio_data;
    }

    public static function audit($post)
    {
        Db::startTrans();
        try {
            $time = time();
            // 审核通过
            $model = new UserLevelApply();
            $detail = $model->field(true)->findOrEmpty((int)$post['id'])->toArray();
            if($detail['status'] !=1){
                static::$error = '不是可审核状态';
                return false;
            }
            $user_id = $detail['user_id'];
            $level_name = UserLevelApply::getLevelName($detail['level']);
            if ($post['audit_status'] == 1) {
                UserLevelApply::update([
                    'status'        => 2,
                    'reason'        => $post['reason'] ?? '',
                    'update_time'   => $time
                ], ['id'=>(int)$post['id']]);

                $son_user_data = User::field('new_level,id,new_level_province,new_level_city,new_level_area,new_level_hos')->where('id',$detail['user_id'])->findOrEmpty()->toArray();

                if($son_user_data['new_level']){
                    self::$error = '已有等级';
                    return false;
                }

                $radio_data = self::radio_data($detail['id']);
                foreach ($radio_data as $v){
                    if(!isset($post[$v['id']]) || floatval($post[$v['id']]) <= 0){
                        self::$error = $v['name'].'抽成错误';
                        return false;
                    }
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

                $reason = $post['reason'] ?? '';
                switch ($detail['level']){
                    case 1:
                        Db::name('user')->where('id',$user_id)->update(['new_level'=>1,'new_level_province'=>$detail['self_id']]);
                        Db::name('user_new_level_log')->insert([
                            'user_id'   => $user_id,
                            'remark'    => '后台审核省代:'.$reason,
                            'content'   => json_encode($detail),
                            'create_time'   => $time
                        ]);
                        Db::name('user_new_level_radio')->insert([
                            'user_id'   => $user_id,
                            'pid'       => 0,
                            'self_id'   => $detail['self_id'],
                            'level'     => 1,
                            'radio'     => $post[$detail['self_id']],
                            'create_time'   => $time,
                        ]);
                        break;
                    case 2:
                        Db::name('user')->where('id',$user_id)->update(['new_level'=>2,'new_level_city'=>$detail['self_id']]);
                        Db::name('user_new_level_log')->insert([
                            'user_id'   => $user_id,
                            'remark'    => '后台审核市代:'.$reason,
                            'content'   => json_encode($detail),
                            'create_time'   => $time
                        ]);
                        $insert_data = [];
                        foreach ($radio_data as $v){
                            $pid = DevRegion::where('id',$v['id'])->value('parent_id');
                            $insert_data[] = [
                                'user_id'   => $user_id,
                                'pid'       => $pid,
                                'self_id'   => $v['id'],
                                'level'     => 2,
                                'radio'     => floatval($post[$v['id']]),
                                'create_time'   => $time,
                            ];

                        }
                        Db::name('user_new_level_radio')->insertAll($insert_data);
                        break;
                    case 3:
                        Db::name('user')->where('id',$user_id)->update(['new_level'=>3,'new_level_area'=>$detail['self_id']]);
                        Db::name('user_new_level_log')->insert([
                            'user_id'   => $user_id,
                            'remark'    => '后台审核区代:'.$reason,
                            'content'   => json_encode($detail),
                            'create_time'   => $time
                        ]);
                        $insert_data = [];
                        foreach ($radio_data as $v){
                            $pid = DevRegion::where('id',$v['id'])->value('parent_id');
                            $insert_data[] = [
                                'user_id'   => $user_id,
                                'pid'       => $pid,
                                'self_id'   => $v['id'],
                                'level'     => 3,
                                'radio'     => floatval($post[$v['id']]),
                                'create_time'   => $time,
                            ];

                        }
                        Db::name('user_new_level_radio')->insertAll($insert_data);
                        break;
                    case 4:
                        Db::name('user')->where('id',$user_id)->update(['new_level'=>4,'new_level_hos'=>$hos_id]);
                        Db::name('user_new_level_log')->insert([
                            'user_id'   => $user_id,
                            'remark'    => '后台审核诊所:'.$reason,
                            'content'   => json_encode($detail),
                            'create_time'   => $time
                        ]);
                        Db::name('user_new_level_radio')->insert([
                            'user_id'   => $user_id,
                            'pid'       => $hos_id,
                            'self_id'   => 0,
                            'level'     => 4,
                            'radio'     => $post[$detail['pid']],
                            'create_time'   => $time,
                        ]);
                        break;
                }
                if($detail['level'] == 4){
                    // $model->where(['level'=>$detail['level'],'pid'=>$detail['pid'],'self_id'=>$detail['self_id'],'status'=>1,'user_id'=>$son_user_data['id']])->where('id','<>',$detail['id'])->update([
                    //     'status' => 3,
                    //     'reason' => '自动驳回其他诊所申请',
                    //     'audit_time' => $time
                    // ]);
                }else{
                    $self_ids = explode(',', $detail['self_id']);//[430111,433100,430822];//
                    $refuse_data = $model
                        // ->where(['level'=>$detail['level'],'status'=>1])
                        ->where(function ($query) use ($self_ids) {
                            foreach ($self_ids as $id) {
                                $query->whereOrRaw("FIND_IN_SET({$id}, self_id)");
                            }
                        })
                        ->select()->toArray();
                        // ->column('id');
                    // var_dump($model->getLastSql());die;
                    if(!empty($refuse_data)){
                        $refuse_id = array_column($refuse_data,'id');
                        $model->where('id','in',$refuse_id)->update([
                            'status' => 3,
                            'reason' => '自动驳回同地区申请',
                            'audit_time' => $time
                        ]);
                        foreach ($refuse_data as $r){
                            $result = '已被拒绝，拒绝理由：区域已有代理';
                            //发送通知
                            Hook::listen('notice', [
                                'user_id' => $r['user_id'],
                                'level_name' => $level_name,
                                'result'    => $result,
                                'scene' => NoticeSetting::NEW_LEVEL_AUDIT,
                            ]);
                        }
                    }
                }
                $result ='已审核通过';
            } else {
                $reason = $post['reason'] ?? '';
                if(!$reason){
                    static::$error = '请填写拒绝理由';
                    return false;
                }
                // 审核拒绝
                UserLevelApply::update([
                    'status'        => 3,
                    'reason'        => $reason,
                    'audit_time'    => $time
                ], ['id'=>(int)$post['id']]);
                $result = '已被拒绝，拒绝理由：'.$reason;
            }
            //发送通知
            Hook::listen('notice', [
                'user_id' => $user_id,
                'level_name' => $level_name,
                'result'    => $result,
                'scene' => NoticeSetting::NEW_LEVEL_AUDIT,
            ]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            static::$error = $e->getMessage();
            return false;
        }
    }
}