<?php


namespace app\admin\logic;

use app\common\model\BabyCheck;
use think\Db;

class BabyCheckLogic
{

    public static function lists($get,$type)
    {
        $model = new BabyCheck();

        $where = [];
        $where[] = ['c.type', '=', $type];
        if (isset($get['title']) && $get['title']) {
            $where[] = ['u.nickname|u.mobile','like', '%' . $get['title'] . '%'];
        }

        if (isset($get['baby_name']) && $get['baby_name']) {
            $where[] = ['b.name', 'like', '%' . $get['baby_name'] . '%'];
        }

        if (isset($get['status']) && $get['status']) {
            if($get['status'] == 2){
                $where[] = ['c.check_result','<>',''];
            }else{
                $where[] = ['c.check_result','=',''];
            }
        }


        $field = 'u.avatar,u.nickname,u.mobile,b.name,b.sex,b.head_img,c.baby_data,c.check_data,c.create_time,c.check_result,c.check_date,c.id';
        $count = $model->alias('c')->leftJoin('user u','u.id = c.user_id')->leftJoin('baby b','b.id = c.baby_id')->where($where)->count();
        $list = $model->alias('c')->leftJoin('user u','u.id = c.user_id')->leftJoin('baby b','b.id = c.baby_id')
            ->field($field)
            ->where($where)->page($get['page'], $get['limit'])->order('c.id desc')->select();
        foreach ($list as &$item) {
            $baby_data = json_decode($item['baby_data'],true);
            $item['age'] = now_age($baby_data['birthday'],$item['check_date']);
            $item['avatar'] = addImgDomain($item['avatar']);
            $item['head_img'] = addImgDomain($item['head_img']);
            $item['user_pdf'] = addImgDomain(json_decode($item['check_data'],true)['url']);
            $item['check_result'] = addImgDomain($item['check_result']);
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['check_date'] = date('Y-m-d', $item['check_date']);
            // $item['cat_name'] = ArticleCategory::where('id',$item['cid'])->value('name');
            if ($item['check_result']) {
                $item['status_text'] = '已测评';
            } else {
                $item['status_text'] = '待上传结果';
            }
            $item['sex_str'] = $item['sex'] == 1?'男':'女';
            unset($item['baby_data']);
            unset($item['check_data']);
        }
        return ['count' => $count, 'lists' => $list];
    }

    public static function upload_pdf($data)
    {
        if(empty($data['pdf'])){
            return '请上传PDF';
        }
        $model = new BabyCheck();
        $check = $model->find($data['id']);
        if(!$check){
            return '测评不存在';
        }
        $check_data = json_decode($check->check_data,true);
        $check_data['time'] = time();
        $check->check_data = json_encode($check_data);
        $check->check_result = clearDomain($data['pdf']);
        return $check->save();
    }
}