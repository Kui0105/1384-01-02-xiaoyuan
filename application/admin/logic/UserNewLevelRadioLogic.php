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

        if (isset($get['district']) && $get['district']) {
            $where[] = ['l.self_id', '=', $get['district']];
        }

        if (isset($get['districts']) && $get['districts']) {
            $where[] = ['l.pid', '=', $get['districts']];
        }

        if (isset($get['keyword']) && $get['keyword']) {
            $where[] = ['u.nickname|u.mobile|n.name|n.mobile', 'like', '%' . $get['keyword'] . '%'];
        }

        $count = $model->alias('l')
            ->leftJoin('user u','u.id = l.user_id')
            ->leftJoin('dev_region r','r.id = l.self_id')
            ->leftJoin('user_new_level n','n.user_id = l.user_id')
            ->where($where)
            ->order('l.id asc')
            ->count();
        $list = $model->alias('l')
            ->leftJoin('user u','u.id = l.user_id')
            ->leftJoin('dev_region r','r.id = l.self_id')
            ->leftJoin('user_new_level n','n.user_id = l.user_id')
            ->field('r.name as self_name,u.nickname,u.mobile as user_mobile,u.avatar,l.*,n.name as n_name,n.mobile as n_mobile')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('l.id asc')
            ->select();

        foreach ($list as &$item){
            switch ($item['level']){
                case 1:
                    $item['area_name'] = DevRegion::getArea($item['self_id']);
                    break;
                case 2:
                    $item['area_name'] = DevRegion::getArea($item['pid']).'-'.DevRegion::getArea($item['self_id']);
                    break;
                case 3:
                    $province_id = DevRegion::where('id',$item['pid'])->value('parent_id');
                    $item['area_name'] = DevRegion::getArea($province_id).'-'.DevRegion::getArea($item['pid']).'-'.DevRegion::getArea($item['self_id']);
                    break;
                case 4:
                    $city_id = DevRegion::where('id',$item['pid'])->value('parent_id');
                    $province_id = DevRegion::where('id',$city_id)->value('parent_id');
                    $item['area_name'] = DevRegion::getArea($province_id).'-'.DevRegion::getArea($city_id).'-'.DevRegion::getArea($item['pid']);
                    $item['address'] = Db::name('user_new_level')->where('id',$item['self_id'])->value('address');
                    break;
            }

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
        $name       = $post_data['name']??'';
        $mobile     = $post_data['mobile']??'';
        if(!$user_id || !$level || !$money || !$remark ||!$name ||!$mobile){
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
                'name'      => $name,
                'mobile'    => $mobile,
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
        $name       = $post_data['name']??'';
        $mobile     = $post_data['mobile']??'';
        if(!$id || !$level || !$money || !$remark || !$name || !$mobile){
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
            Db::name('user_new_level')->where(['user_id'=>$user_id,'level'=>1])->update(['self_id'=>$level,'name'=>$name,'mobile'=>$mobile]);
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
        $level_data = Db::name('user_new_level')->where('user_id',$radio_data['user_id'])->find();
        $radio_data['avatar'] = addImgDomain($radio_data['avatar']);
        $radio_data['name'] = $level_data['name'];
        $radio_data['mobile'] = $level_data['mobile'];
        if($radio_data['level'] == 4){
            $radio_data['city'] = DevRegion::where('id',$radio_data['pid'])->value('parent_id');
            $radio_data['province'] = DevRegion::where('id',$radio_data['city'])->value('parent_id');
            $radio_data['district']  = $radio_data['pid'];
            $radio_data['latitude'] = $level_data['latitude'];
            $radio_data['longitude'] = $level_data['longitude'];
            $radio_data['address'] = $level_data['address'];
        }
        // $radio_data['self_name'] = DevRegion::getArea($radio_data['self_id']);
        return $radio_data;
    }

    public static function adjustCity($post_data)
    {
        $user_id    = $post_data['user_id']??0;
        $level      = $post_data['level']??0;
        $remark     = $post_data['remark']??'';
        $money      = $post_data['money']??0;
        $name       = $post_data['name']??'';
        $mobile     = $post_data['mobile']??'';
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
        }else{
            if(!$name || !$mobile){
                return '请填写姓名电话';
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
                if($name){
                    $level_update['name'] = $name;
                }
                if($mobile){
                    $level_update['mobile'] = $mobile;
                }
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
                    'name'      => $name,
                    'mobile'    => $mobile,
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
        $name       = $post_data['name']??'';
        $mobile     = $post_data['mobile']??'';
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
                'pid'       => $pid,
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
                $level_update = ['pid'=>$pid,'self_id'=>$level];
                if($name){
                    $level_update['name'] = $name;
                }
                if($mobile){
                    $level_update['mobile'] = $mobile;
                }
                Db::name('user_new_level')->where(['user_id'=>$user_id,'level'=>2])->update($level_update);
                Db::name('user')->where('id',$user_id)->update(['new_level'=>2,'new_level_city'=>$level]);
            }else{
                if($radio_data['pid'] != $pid){
                    return '非原归属省份';
                }
                $self_str  = implode(',',$user_city_arr).','.$level;
                $level_update = ['self_id'=>$self_str];
                if($name){
                    $level_update['name'] = $name;
                }
                if($mobile){
                    $level_update['mobile'] = $mobile;
                }
                Db::name('user_new_level')->where(['user_id'=>$radio_data['user_id'],'level'=>2])->update($level_update);
                Db::name('user')->where('id',$radio_data['user_id'])->update(['new_level_city'=>$self_str]);
            }

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    public static function adjustArea($post_data)
    {
        $user_id    = $post_data['user_id']??0;
        $level      = $post_data['level']??0;
        $remark     = $post_data['remark']??'';
        $money      = $post_data['money']??0;
        $name       = $post_data['name']??'';
        $mobile     = $post_data['mobile']??'';
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
            if($user['new_level'] !=3){
                return '用户已有其他等级';
            }
        }else{
            if(!$name || !$mobile){
                return '请填写姓名电话';
            }
        }
        $now_city_arr = Db::name('user_new_level_radio')->where('level',3)->column('self_id');//->where('level','in',[1,2,3])
        if(in_array($level,$now_city_arr)){
            return '该区域已有代理';
        }
        $area_data = DevRegion::where('id',$level)->field('parent_id,level')->findOrEmpty();
        if(empty($area_data) || $area_data['level'] !=3){
            return '区域错误';
        }
        $pid = $area_data['parent_id'];
        $province_id = DevRegion::where('id',$pid)->value('parent_id');

        Db::startTrans();
        try {
            $time = time();
            if($user['new_level']){
                $level_data = Db::name('user_new_level')->where(['user_id'=>$user_id,'level'=>3])->find();
                // if($level_data['pid'] != $pid){
                //     return '非原归属城市';
                // }
                $now_province_id = DevRegion::where('id','in',$level_data['pid'])->value('parent_id');

                if($province_id != $now_province_id){
                    return '不可跨省';
                }
                $now_pid_str = $level_data['pid'];
                $now_pid_arr = explode(',',$now_pid_str);

                $user_update = [
                    'new_level_area' => $user['new_level_area'].','.$level
                ];
                $level_update = [
                    'self_id'   => $level_data['self_id'].','.$level
                ];
                if(!in_array($pid,$now_pid_arr)){
                    $level_update['pid'] = $now_pid_str.','.$pid;
                }
                if($name){
                    $level_update['name'] = $name;
                }
                if($mobile){
                    $level_update['mobile'] = $mobile;
                }
                Db::name('user_new_level')->where('id',$level_data['id'])->update($level_update);
            }else{
                $user_update = [
                    'new_level'             => 3,
                    'new_level_area'        => $level
                ];
                Db::name('user_new_level')->insert([
                    'user_id'   => $user_id,
                    'pid'       => $pid,
                    'self_id'   => $level,
                    'level'     => 3,
                    'name'      => $name,
                    'mobile'    => $mobile,
                    'create_time'   => $time,
                ]);
            }
            Db::name('user')->where('id',$user_id)->update($user_update);

            Db::name('user_new_level_log')->insert([
                'user_id'   => $user_id,
                'remark'    => '后台添加区代:'.$remark,
                'content'   => json_encode($post_data),
                'create_time'   => $time
            ]);
            Db::name('user_new_level_radio')->insert([
                'user_id'   => $user_id,
                'pid'       => $pid,
                'self_id'   => $level,
                'level'     => 3,
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

    public static function edit_area($post_data)
    {
        $id         = $post_data['id']??0;
        $level      = $post_data['level']??0;
        $remark     = $post_data['remark']??'';
        $money      = $post_data['money']??0;
        $name       = $post_data['name']??'';
        $mobile     = $post_data['mobile']??'';
        if(!$id || !$level || !$money || !$remark){
            return '请填写完整';
        }
        if($money < 0){
            return '抽成比例错误';
        }
        $radio_data = Db::name('user_new_level_radio')->where('id',$id)->find();
        if(!$radio_data){
            self::$error = '区代不存在';
            return false;
        }

        $now_city_arr = Db::name('user_new_level_radio')->where('level',3)->where('id','<>',$id)->column('self_id');//->where('level','in',[1,2,3])
        if(in_array($level,$now_city_arr)){
            return '该区域已有代理';
        }
        $area_data = DevRegion::where('id',$level)->field('parent_id,level')->findOrEmpty();
        if(empty($area_data) || $area_data['level'] !=3){
            return '区域错误';
        }
        $pid = $area_data['parent_id'];
        Db::startTrans();
        try {
            $user_id = $radio_data['user_id'];
            $time = time();

            Db::name('user_new_level_radio')->where('id',$id)->update([
                'pid'       => $pid,
                'self_id'   => $level,
                'radio'     => $money,
            ]);
            Db::name('user_new_level_log')->insert([
                'user_id'   => $user_id,
                'remark'    => '后台编辑区代:'.$remark,
                'content'   => json_encode($radio_data),
                'create_time'   => $time
            ]);
            $user_area_arr = Db::name('user_new_level_radio')->where('user_id',$radio_data['user_id'])->where('id','<>',$id)->column('self_id');

            if(empty($user_area_arr)){
                $level_update = ['pid'=>$pid,'self_id'=>$level];
                if($name){
                    $level_update['name'] = $name;
                }
                if($mobile){
                    $level_update['mobile'] = $mobile;
                }
                Db::name('user_new_level')->where(['user_id'=>$user_id,'level'=>3])->update($level_update);
                Db::name('user')->where('id',$user_id)->update(['new_level'=>3,'new_level_area'=>$level]);
            }else{
                $province_id = DevRegion::where('id',$pid)->value('parent_id');
                $level_data = Db::name('user_new_level')->where(['user_id'=>$user_id,'level'=>3])->find();
                $now_province_id = DevRegion::where('id','in',$level_data['pid'])->value('parent_id');
                if($province_id != $now_province_id){
                    return '不可跨省';
                }
                $self_str  = implode(',',$user_area_arr).','.$level;
                $now_pid_str = $level_data['pid'];
                $now_pid_arr = explode(',',$now_pid_str);
                $level_update = [
                    'self_id'   => $self_str
                ];
                if(!in_array($pid,$now_pid_arr)){
                    $level_update['pid'] = $now_pid_str.','.$pid;
                }
                if($name){
                    $level_update['name'] = $name;
                }
                if($mobile){
                    $level_update['mobile'] = $mobile;
                }
                // if($radio_data['pid'] != $pid){
                //     return '非原归属城市';
                // }

                Db::name('user_new_level')->where(['user_id'=>$radio_data['user_id'],'level'=>3])->update($level_update);
                Db::name('user')->where('id',$radio_data['user_id'])->update(['new_level_area'=>$self_str]);
            }

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    public static function del_area($params)
    {
        $id = $params['id']??0;
        $radio_data = Db::name('user_new_level_radio')->where('id',$id)->find();
        if(!$radio_data){
            self::$error = '区代不存在';
            return false;
        }
        Db::startTrans();
        try {
            $time = time();
            $user_city_arr = Db::name('user_new_level_radio')->where('user_id',$radio_data['user_id'])->where('id','<>',$id)->column('self_id');
            Db::name('user_new_level_radio')->where('id',$id)->delete();
            Db::name('user_new_level_log')->insert([
                'user_id'   => $radio_data['user_id'],
                'remark'    => '后台移除区代',
                'content'   => json_encode($radio_data),
                'create_time'   => $time
            ]);
            if(empty($user_city_arr)){
                Db::name('user_new_level')->where(['user_id'=>$radio_data['user_id'],'level'=>3])->delete();
                Db::name('user')->where('id',$radio_data['user_id'])->update(['new_level'=>0,'new_level_area'=>'']);
            }else{
                $self_str  = implode(',',$user_city_arr);
                Db::name('user_new_level')->where(['user_id'=>$radio_data['user_id'],'level'=>3])->update(['self_id'=>$self_str]);
                Db::name('user')->where('id',$radio_data['user_id'])->update(['new_level_area'=>$self_str]);
            }
            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function adjustHos($post_data)
    {
        $user_id    = $post_data['user_id']??0;
        $province   = $post_data['province']??0;
        $city       = $post_data['city']??0;
        $district   = $post_data['district']??0;
        $address    = $post_data['address']??0;
        $longitude  = $post_data['longitude']??'';
        $latitude   = $post_data['latitude']??'';
        $remark     = $post_data['remark']??'';
        $money      = $post_data['money']??0;
        $name       = $post_data['name']??'';
        $mobile     = $post_data['mobile']??'';
        if(!$user_id ){
            return '请选择用户';
        }
        if(!$name){
            return '请输入姓名';
        }
        if(!$mobile){
            return '请输入电话';
        }
        if(!$province || !$city || !$district){
            return '请选择地区';
        }
        if(!$address || !$longitude || !$latitude){
            return '请选择位置';
        }
        if(!$money){
            return '请输入抽成比例';
        }
        if($money < 0){
            return '抽成比例错误';
        }
        if(!$remark){
            return '请输入备注';
        }
        $user =  User::field('id,new_level,new_level_province,new_level_city,new_level_area,new_level_hos')
            ->findOrEmpty($user_id);
        if($user->isEmpty()) {
            return '用户不存在';
        }
        if($user['new_level']){
            if($user['new_level'] == 4){
                return '用户已是诊所';
            }
            return '用户已有其他等级';
        }
        $area_data = DevRegion::where('id',$district)->field('parent_id,level')->findOrEmpty();
        if(empty($area_data) || $area_data['level'] !=3){
            return '区域错误';
        }
        Db::startTrans();
        try {
            $time = time();
            $hos_id = Db::name('user_new_level')->insertGetId([
                'user_id'   => $user_id,
                'name'      => $name,
                'mobile'    => $mobile,
                'pid'       => $district,
                'longitude' => $longitude,
                'latitude'  => $latitude,
                'address'   => $address,
                'self_id'   => 0,
                'level'     => 4,
                'create_time'   => $time,
            ]);
            $user_update = [
                'new_level'             => 4,
                'new_level_hos'         => $hos_id,
                'hos_p_area_id'         => $district,
            ];
            Db::name('user')->where('id',$user_id)->update($user_update);
            Db::name('user_new_level_log')->insert([
                'user_id'   => $user_id,
                'remark'    => '后台添加诊所:'.$remark,
                'content'   => json_encode($post_data),
                'create_time'   => $time
            ]);
            Db::name('user_new_level_radio')->insert([
                'user_id'   => $user_id,
                'pid'       => $district,
                'self_id'   => $hos_id,
                'level'     => 4,
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

    public static function edit_hos($post_data)
    {
        $id         = $post_data['id']??0;
        $province   = $post_data['province']??0;
        $city       = $post_data['city']??0;
        $district   = $post_data['district']??0;
        $address    = $post_data['address']??0;
        $longitude  = $post_data['longitude']??'';
        $latitude   = $post_data['latitude']??'';
        $remark     = $post_data['remark']??'';
        $money      = $post_data['money']??0;
        $name       = $post_data['name']??'';
        $mobile     = $post_data['mobile']??'';
        if(!$name){
            return '请输入姓名';
        }
        if(!$mobile){
            return '请输入电话';
        }
        if(!$province || !$city || !$district){
            return '请选择地区';
        }
        if(!$address || !$longitude || !$latitude){
            return '请选择位置';
        }
        if(!$money){
            return '请输入抽成比例';
        }
        if($money < 0){
            return '抽成比例错误';
        }
        if(!$remark){
            return '请输入备注';
        }

        $radio_data = Db::name('user_new_level_radio')->where('id',$id)->find();
        if(!$radio_data){
            self::$error = '诊所不存在';
            return false;
        }

        $area_data = DevRegion::where('id',$district)->field('parent_id,level')->findOrEmpty();
        if(empty($area_data) || $area_data['level'] !=3){
            return '区域错误';
        }

        Db::startTrans();
        try {
            $user_id = $radio_data['user_id'];
            $time = time();

            Db::name('user_new_level_radio')->where('id',$id)->update([
                'pid'       => $district,
                'radio'     => $money,
            ]);

            Db::name('user')->where('id',$user_id)->update(['hos_p_area_id'=> $district]);
            Db::name('user_new_level_log')->insert([
                'user_id'   => $user_id,
                'remark'    => '后台编辑诊所:'.$remark,
                'content'   => json_encode($radio_data),
                'create_time'   => $time
            ]);

            Db::name('user_new_level')->where('id',$radio_data['self_id'])->update([
                'pid'       => $district,
                'longitude' => $longitude,
                'latitude'  => $latitude,
                'name'      => $name,
                'mobile'    => $mobile,
                'address'   => $address
            ]);

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    public static function del_hos($params)
    {
        $id = $params['id']??0;
        $radio_data = Db::name('user_new_level_radio')->where('id',$id)->find();
        if(!$radio_data){
            self::$error = '诊所不存在';
            return false;
        }
        Db::startTrans();
        try {
            $time = time();

            Db::name('user_new_level_radio')->where('id',$id)->delete();
            Db::name('user_new_level_log')->insert([
                'user_id'   => $radio_data['user_id'],
                'remark'    => '后台移除诊所',
                'content'   => json_encode($radio_data),
                'create_time'   => $time
            ]);
            Db::name('user_new_level')->where('id',$radio_data['self_id'])->delete();
            Db::name('user')->where('id',$radio_data['user_id'])->update(['new_level'=>0,'new_level_hos'=>0,'hos_p_area_id'=>0]);
            Db::name('user')->where('first_leader',$radio_data['user_id'])->update(['first_leader'=>0]);
            Db::name('hos_doctor')->where('hos_id',$radio_data['self_id'])->delete();
            Db::name('hos_info')->where('hos_id',$radio_data['self_id'])->delete();
            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }
}