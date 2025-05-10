<?php


namespace app\admin\logic;

use app\common\model\HosDoctor;
use think\Db;

class HosDoctorLogic
{

    /**
     * 宝宝列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function lists($get)
    {
        $model = new HosDoctor();
        $where = [];
        $where[] = ['hos_id', '=', $get['hos_id']];


        if (isset($get['name']) && $get['name']) {
            $where[] = ['name', 'like', '%' . $get['name'] . '%'];
        }

        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['b.create_time', '>=', strtotime($get['start_time'])];
        }
        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['b.create_time', '<=', strtotime($get['end_time'])];
        }

        $data_count = $model
            ->where($where)
            ->count();
        $data_list = $model
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('id desc')
            ->select();

        foreach ($data_list as &$item) {
            $item['imgs'] = addImgDomain($item['imgs']);
            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
        }
        return ['count' => $data_count, 'list' => $data_list];
    }

    public static function add_doctor($post,$hos_id)
    {
        $name = $post['name']??'';
        $imgs = $post['imgs']??'';
        $intro = $post['intro']??'';
        if(!$name){
            return '请输入医生名称';
        }
        if(!$imgs){
            return '请上传医生照片';
        }
        if(!$intro){
            return '请填写擅长领域';
        }
        $time = time();
        if(!$hos_id){
            return '诊所错误';
        }
        $hos_info = Db::name('user_new_level')->where('id',$hos_id)->find();
        if(!$hos_info){
            return '诊所错误';
        }
        $new_data = [
            'name'          => $name,
            'imgs'          => clearDomain($imgs),
            'intro'         => $intro,
            'user_id'       => $hos_info['user_id'],
            'hos_id'        => $hos_id,
            'create_time'   => $time,
            'update_time'   => $time
        ];
        Db::name('hos_doctor')->insert($new_data);
        return true;
    }

    public static function getInfo($id)
    {
        $info = Db::name('hos_doctor')->where('id',$id)->find();
        if(!$info){
            return [
                'id'    => 0,
                'imgs'  => '',
                'name'  => '',
                'intro' => '',
            ];
        }
        $info['imgs'] = addImgDomain($info['imgs']);
        return $info;
    }

    public static function edit_doctor($post)
    {
        $id = $post['id']??0;
        $name = $post['name']??'';
        $imgs = $post['imgs']??'';
        $intro = $post['intro']??'';
        if(!$name){
            return '请输入医生名称';
        }
        if(!$imgs){
            return '请上传医生照片';
        }
        if(!$intro){
            return '请填写擅长领域';
        }
        $time = time();
        if(!$id){
            return '医生错误';
        }
        $doctor_info = Db::name('hos_doctor')->where('id',$id)->find();
        if(!$doctor_info){
            return '诊所错误';
        }
        $new_data = [
            'name'          => $name,
            'imgs'          => clearDomain($imgs),
            'intro'         => $intro,
            'update_time'   => $time
        ];
        Db::name('hos_doctor')->where('id',$id)->update($new_data);
        return true;
    }

    public static function del_doctor($params)
    {
        $id = $params['id']??0;
        $res = Db::name('hos_doctor')->where('id',$id)->delete();
        return $res == 1?true:'失败';
    }
}