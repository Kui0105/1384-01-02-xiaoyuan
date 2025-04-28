<?php


namespace app\admin\logic;

use app\common\model\Baby;
use app\common\model\BabyCheck;
use app\common\server\AreaServer;
use think\Db;

class BabyLogic
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
        $baby = new Baby();
        $where = [];
        $where[] = ['b.del', '=', 0];

        if (isset($get['keyword']) && $get['keyword']) {
            $where[] = ['u.nickname|u.mobile', 'like', '%' . $get['keyword'] . '%'];
        }

        if (isset($get['name']) && $get['name']) {
            $where[] = ['b.name', 'like', '%' . $get['name'] . '%'];
        }

        if (isset($get['sex']) && $get['sex']) {
            $where[] = ['b.sex', '=', $get['sex']];
        }

        if (isset($get['user_id']) && $get['user_id']) {
            $where[] = ['b.user_id', '=', $get['user_id']];
        }

        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['b.create_time', '>=', strtotime($get['start_time'])];
        }
        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['b.create_time', '<=', strtotime($get['end_time'])];
        }

        $baby_count = $baby
            ->alias('b')
            ->leftJoin('user u','u.id = b.user_id')
            ->where($where)
            ->count();
        $baby_list = $baby
            ->alias('b')
            ->leftJoin('user u','u.id = b.user_id')
            ->field('u.avatar,u.nickname,u.mobile,b.*')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order(['b.id' => 'desc','b.user_id' => 'desc'])
            ->select();

        foreach ($baby_list as $item) {
            $item['avatar'] = addImgDomain($item['avatar']);
            $item['head_img'] = addImgDomain($item['head_img']);
            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
            $item['sex_str'] = $item['sex'] ==1?'男':'女';
            $item['birthday_str'] = date('Y-m-d',$item['birthday']);
            $item['address'] = AreaServer::getAddress([$item['province_id'], $item['city_id']]);
        }
        return ['count' => $baby_count, 'list' => $baby_list];
    }

    /**
     * 宝宝历史测评列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function history_check($get)
    {
        $check = new BabyCheck();
        $where = [];

        if (isset($get['baby_id']) && $get['baby_id']) {
            $where[] = ['c.baby_id', '=', $get['baby_id']];
        }

        // if (isset($get['name']) && $get['name']) {
        //           $where[] = ['b.name', 'like', '%' . $get['name'] . '%'];
        //       }

        // if (isset($get['sex']) && $get['sex']) {
        //           $where[] = ['b.sex', '=', $get['sex']];
        //       }

        //       if (isset($get['user_id']) && $get['user_id']) {
        //           $where[] = ['b.user_id', '=', $get['user_id']];
        //       }

        // if (isset($get['start_time']) && $get['start_time'] != '') {
        //           $where[] = ['b.create_time', '>=', strtotime($get['start_time'])];
        //       }
        //       if (isset($get['end_time']) && $get['end_time'] != '') {
        //           $where[] = ['b.create_time', '<=', strtotime($get['end_time'])];
        //       }

        $baby_count = $check
            ->alias('c')
            ->leftJoin('baby b','b.id = c.baby_id')
            ->where($where)
            ->count();
        $baby_list = $check
            ->alias('c')
            ->leftJoin('baby b','b.id = c.baby_id')
            ->field('b.name,c.*')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order(['c.check_date'=>'desc','c.id' => 'desc'])
            ->select();

        foreach ($baby_list as &$item) {

            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
            $check_data = json_decode($item['check_data'],true);
            $item['height'] = $check_data['height'];
            $item['weight'] = $check_data['weight'];
            $item['bone'] = $check_data['bone']??'';
            $item['last_date'] = $check_data['last_date']??'';
            $item['last_height'] = $check_data['last_height']??'';
            $now_age_str = $check_data['now_age_str'];
            $baby_data = json_decode($item['baby_data'],true);
            $item['age'] = now_age($baby_data['birthday'],$item['check_date']);
            // $item['age_str'] =
            $item['check_date'] = date('Y-m-d',$item['check_date']);
            // $item['avatar'] = addImgDomain($item['avatar']);
            // $item['head_img'] = addImgDomain($item['head_img']);
            // $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
            // $item['sex_str'] = $item['sex'] ==1?'男':'女';
            // $item['birthday_str'] = date('Y-m-d',$item['birthday']);
        }
        return ['count' => $baby_count, 'list' => $baby_list];
    }
}