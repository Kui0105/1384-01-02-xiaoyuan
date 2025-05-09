<?php

namespace app\admin\logic;
use app\common\logic\LogicBase;
use think\Exception;
use app\common\model\{User, UserNewLevelRadio, DevRegion};
use app\common\server\UrlServer;
use think\Db;

class HosInfoLogic extends LogicBase
{
    public static function getInfo($id)
    {
        $info = Db::name('hos_info')->where('hos_id',$id)->find();
        if(!$info){
            return [
                'id'    => 0,
                'imgs'  => '',
                'name'  => '',
                'intro' => '',
                'hos_id'    => $id
            ];
        }
        $info['imgs'] = addImgDomain($info['imgs']);
        return $info;
    }

    public static function edit($post)
    {
        $id = $post['id']??0;
        $hos_id = $post['hos_id']??0;
        $name = $post['name']??'';
        $imgs = $post['imgs']??'';
        $intro = $post['intro']??'';
        if(!$name){
            return '请输入门店名称';
        }
        if(!$imgs){
            return '请上传门店照片';
        }
        if(!$intro){
            return '请输入门店简介';
        }
        $time = time();
        if($id){
            $new_data = [
                'name'  => $name,
                'imgs'  => clearDomain($imgs),
                'intro' => $intro,
                'update_time' => $time
            ];
            $insert = false;
        }else{
            if(!$hos_id){
                return '诊所错误';
            }
            $hos_info = Db::name('user_new_level')->where('id',$hos_id)->find();
            if(!$hos_info){
                return '诊所错误';
            }
            $new_data = [
                'name'  => $name,
                'imgs'  => clearDomain($imgs),
                'intro' => $intro,
                'user_id'       => $hos_info['user_id'],
                'hos_id'        => $hos_id,
                'create_time'   => $time,
                'update_time'   => $time
            ];
            $insert = true;
        }
        if($insert === true){
            Db::name('hos_info')->insert($new_data);
        }else{
            Db::name('hos_info')->where('id',$id)->update($new_data);
        }
        return true;
    }
}