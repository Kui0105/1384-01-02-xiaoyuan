<?php

namespace app\admin\logic;
use app\common\logic\LogicBase;
use think\Exception;
use app\common\model\{User, UserNewLevelRadio, DevRegion};
use app\common\server\UrlServer;
use think\Db;

class UserNewLevelRadioLogic extends LogicBase
{
    public static function lists($get){
        $model = new UserNewLevelRadio();

        $where = [];
        $where[] = ['l.level', '=', $get['level']];

        if (isset($get['province']) && $get['province']) {
            $where[] = ['l.self_id', '=', $get['province']];
        }

        if (isset($get['city']) && $get['city']) {
            $where[] = ['l.self_id', '=', $get['city']];
        }

        if (isset($get['keyword']) && $get['keyword']) {
            $where[] = ['u.nickname|u.mobile', 'like', '%' . $get['keyword'] . '%'];
        }

        $count = $model->alias('l')
            ->leftJoin('user u','u.id = l.user_id')
            ->leftJoin('dev_region r','r.id = l.self_id')
            ->where($where)
            ->order('l.id asc')
            ->count();
        $list = $model->alias('l')
            ->leftJoin('user u','u.id = l.user_id')
            ->leftJoin('dev_region r','r.id = l.self_id')
            ->field('r.name as self_name,u.nickname,u.mobile as user_mobile,u.avatar,l.*')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('l.id asc')
            ->select();

        foreach ($list as &$item){
            $item['area_name'] = DevRegion::getArea($item['self_id']);
            $item['avatar'] = addImgDomain($item['avatar']);
            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
        }

        return ['count' => $count, 'lists' => $list];
    }


    public static function adjustProvince($post_data)
    {
        $user_id    = $post_data['user_id']??0;
        $level      = $post_data['level']??0;
        $remark     = $post_data['remark']??'';
        $money      = $post_data['money']??0;
        if(!$user_id || !$level || !$money || !$remark){
            return '请填写完整';
        }
        if($money < 0){
            return '抽成比例错误';
        }
        $user =  User::field('id,new_level,new_level_province,new_level_city,new_level_area')
            ->findOrEmpty($user_id);
        if($user->isEmpty()) {
            return '用户不存在';
        }
        if($user['new_level']){
            return '用户已有等级';
        }
        $now_province_arr = Db::name('user_new_level_radio')->where('level',1)->column('self_id');//->where('level','in',[1,2,3])
        if(in_array($level,$now_province_arr)){
            return '该省份已有代理';
        }
        $area_level = DevRegion::where('id',$level)->value('level');
        if(!$area_level || $area_level !=1){
            return '省份错误';
        }
        Db::startTrans();
        try {
            $time = time();
            Db::name('user')->where('id',$user_id)->update(['new_level'=>1,'new_level_province'=>$level]);
            Db::name('user_new_level')->insert([
                'user_id'   => $user_id,
                'pid'       => 0,
                'self_id'   => $level,
                'level'     => 1,
                'create_time'   => $time,
            ]);
            Db::name('user_new_level_log')->insert([
                'user_id'   => $user_id,
                'remark'    => '后台添加省代:'.$remark,
                'content'   => json_encode($post_data),
                'create_time'   => $time
            ]);
            Db::name('user_new_level_radio')->insert([
                'user_id'   => $user_id,
                'pid'       => 0,
                'self_id'   => $level,
                'level'     => 1,
                'radio'     => $money,
                'create_time'   => $time,
            ]);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    public static function edit_province($post_data)
    {

        $id         = $post_data['id']??0;
        $level      = $post_data['level']??0;
        $remark     = $post_data['remark']??'';
        $money      = $post_data['money']??0;
        if(!$id || !$level || !$money || !$remark){
            return '请填写完整';
        }
        if($money < 0){
            return '抽成比例错误';
        }
        $radio_data = Db::name('user_new_level_radio')->where('id',$id)->find();
        if(!$radio_data){
            self::$error = '省代不存在';
            return false;
        }

        $now_province_arr = Db::name('user_new_level_radio')->where('level',1)->where('id','<>',$id)->column('self_id');//->where('level','in',[1,2,3])
        if(in_array($level,$now_province_arr)){
            return '该省份已有代理';
        }
        $area_level = DevRegion::where('id',$level)->value('level');
        if(!$area_level || $area_level !=1){
            return '省份错误';
        }
        Db::startTrans();
        try {
            $user_id = $radio_data['user_id'];
            $time = time();
            Db::name('user')->where('id',$user_id)->update(['new_level'=>1,'new_level_province'=>$level]);
            Db::name('user_new_level')->where(['user_id'=>$user_id,'level'=>1])->update(['self_id'=>$level]);
            Db::name('user_new_level_log')->insert([
                'user_id'   => $user_id,
                'remark'    => '后台编辑省代:'.$remark,
                'content'   => json_encode($radio_data),
                'create_time'   => $time
            ]);
            Db::name('user_new_level_radio')->where('id',$id)->update([
                'self_id'   => $level,
                'radio'     => $money,
            ]);

            Db::commit();

            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    public static function  del_province($params)
    {
        $id = $params['id']??0;
        $radio_data = Db::name('user_new_level_radio')->where('id',$id)->find();
        if(!$radio_data){
            self::$error = '省代不存在';
            return false;
        }
        Db::startTrans();
        try {
            $time = time();
            Db::name('user_new_level')->where(['user_id'=>$radio_data['user_id'],'level'=>1])->delete();
            Db::name('user_new_level_radio')->where('id',$id)->delete();
            Db::name('user_new_level_log')->insert([
                'user_id'   => $radio_data['user_id'],
                'remark'    => '后台移除省代',
                'content'   => json_encode($radio_data),
                'create_time'   => $time
            ]);
            Db::name('user')->where('id',$radio_data['user_id'])->update(['new_level'=>0,'new_level_province'=>'']);
            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function getInfo($params)
    {
        $id = $params['id']??0;
        $radio_data = Db::name('user_new_level_radio')->alias('r')->leftJoin('user u','u.id = r.user_id')->where('r.id',$id)->field('r.*,u.nickname,u.mobile,u.avatar')->find();
        if(!$radio_data){
            return [];
        }
        $radio_data['avatar'] = addImgDomain($radio_data['avatar']);
        // $radio_data['self_name'] = DevRegion::getArea($radio_data['self_id']);
        return $radio_data;
    }

    public static function adjustCity($post_data)
    {
        $user_id    = $post_data['user_id']??0;
        $level      = $post_data['level']??0;
        $remark     = $post_data['remark']??'';
        $money      = $post_data['money']??0;
        if(!$user_id || !$level || !$money || !$remark){
            return '请填写完整';
        }
        if($money < 0){
            return '抽成比例错误';
        }
        $user =  User::field('id,new_level,new_level_province,new_level_city,new_level_area')
            ->findOrEmpty($user_id);
        if($user->isEmpty()) {
            return '用户不存在';
        }
        if($user['new_level']){
            if($user['new_level'] !=2){
                return '用户已有其他等级';
            }
        }
        $now_city_arr = Db::name('user_new_level_radio')->where('level',2)->column('self_id');//->where('level','in',[1,2,3])
        if(in_array($level,$now_city_arr)){
            return '该城市已有代理';
        }
        $area_data = DevRegion::where('id',$level)->field('parent_id,level')->findOrEmpty();
        if(empty($area_data) || $area_data['level'] !=2){
            return '城市错误';
        }
        $pid = $area_data['parent_id'];
        Db::startTrans();
        try {
            $time = time();
            if($user['new_level']){
                $level_data = Db::name('user_new_level')->where(['user_id'=>$user_id,'level'=>2])->find();
                if($level_data['pid'] != $pid){
                    return '非原归属省份';
                }
                $user_update = [
                    'new_level_city' => $user['new_level_city'].','.$level
                ];
                $level_update = [
                    'self_id'   => $level_data['self_id'].','.$level
                ];
                Db::name('user_new_level')->where('id',$level_data['id'])->update($level_update);
            }else{
                $user_update = [
                    'new_level'             => 2,
                    'new_level_city'        => $level
                ];
                Db::name('user_new_level')->insert([
                    'user_id'   => $user_id,
                    'pid'       => $pid,
                    'self_id'   => $level,
                    'level'     => 2,
                    'create_time'   => $time,
                ]);
            }
            Db::name('user')->where('id',$user_id)->update($user_update);

            Db::name('user_new_level_log')->insert([
                'user_id'   => $user_id,
                'remark'    => '后台添加市代:'.$remark,
                'content'   => json_encode($post_data),
                'create_time'   => $time
            ]);
            Db::name('user_new_level_radio')->insert([
                'user_id'   => $user_id,
                'pid'       => $pid,
                'self_id'   => $level,
                'level'     => 2,
                'radio'     => $money,
                'create_time'   => $time,
            ]);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    public static function  del_city($params)
    {
        $id = $params['id']??0;
        $radio_data = Db::name('user_new_level_radio')->where('id',$id)->find();
        if(!$radio_data){
            self::$error = '市代不存在';
            return false;
        }
        Db::startTrans();
        try {
            $time = time();
            $user_city_arr = Db::name('user_new_level_radio')->where('user_id',$radio_data['user_id'])->where('id','<>',$id)->column('self_id');
            Db::name('user_new_level_radio')->where('id',$id)->delete();
            Db::name('user_new_level_log')->insert([
                'user_id'   => $radio_data['user_id'],
                'remark'    => '后台移除市代',
                'content'   => json_encode($radio_data),
                'create_time'   => $time
            ]);
            if(empty($user_city_arr)){
                Db::name('user_new_level')->where(['user_id'=>$radio_data['user_id'],'level'=>2])->delete();
                Db::name('user')->where('id',$radio_data['user_id'])->update(['new_level'=>0,'new_level_city'=>'']);
            }else{
                $self_str  = implode(',',$user_city_arr);
                Db::name('user_new_level')->where(['user_id'=>$radio_data['user_id'],'level'=>2])->update(['self_id'=>$self_str]);
                Db::name('user')->where('id',$radio_data['user_id'])->update(['new_level_city'=>$self_str]);
            }
            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function edit_city($post_data)
    {
        $id         = $post_data['id']??0;
        $level      = $post_data['level']??0;
        $remark     = $post_data['remark']??'';
        $money      = $post_data['money']??0;
        if(!$id || !$level || !$money || !$remark){
            return '请填写完整';
        }
        if($money < 0){
            return '抽成比例错误';
        }
        $radio_data = Db::name('user_new_level_radio')->where('id',$id)->find();
        if(!$radio_data){
            self::$error = '市代不存在';
            return false;
        }

        $now_city_arr = Db::name('user_new_level_radio')->where('level',2)->where('id','<>',$id)->column('self_id');//->where('level','in',[1,2,3])
        if(in_array($level,$now_city_arr)){
            return '该城市已有代理';
        }
        $area_data = DevRegion::where('id',$level)->field('parent_id,level')->findOrEmpty();
        if(empty($area_data) || $area_data['level'] !=2){
            return '城市错误';
        }
        $pid = $area_data['parent_id'];
        Db::startTrans();
        try {
            $user_id = $radio_data['user_id'];
            $time = time();

            Db::name('user_new_level_radio')->where('id',$id)->update([
                'self_id'   => $level,
                'radio'     => $money,
            ]);
            Db::name('user_new_level_log')->insert([
                'user_id'   => $user_id,
                'remark'    => '后台编辑市代:'.$remark,
                'content'   => json_encode($radio_data),
                'create_time'   => $time
            ]);
            $user_city_arr = Db::name('user_new_level_radio')->where('user_id',$radio_data['user_id'])->where('id','<>',$id)->column('self_id');

            if(empty($user_city_arr)){
                Db::name('user_new_level')->where(['user_id'=>$user_id,'level'=>2])->update(['pid'=>$pid,'self_id'=>$level]);
                Db::name('user')->where('id',$user_id)->update(['new_level'=>2,'new_level_province'=>$level]);
            }else{
                if($radio_data['pid'] != $pid){
                    return '非原归属省份';
                }
                $self_str  = implode(',',$user_city_arr).','.$level;
                Db::name('user_new_level')->where(['user_id'=>$radio_data['user_id'],'level'=>2])->update(['self_id'=>$self_str]);
                Db::name('user')->where('id',$radio_data['user_id'])->update(['new_level_city'=>$self_str]);
            }

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
}