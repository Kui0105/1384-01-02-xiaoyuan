<?php


namespace app\api\logic;

use app\common\logic\LogicBase;
use app\common\model\Baby;
use app\common\server\ConfigServer;
use think\Db;

class BabyLogic extends LogicBase{

    public static function add_baby($user_id,$data){
        $cache = cache('baby_add_'.$user_id);
        if($cache){
            self::$error = '操作频繁';
            return  false;
        }
        cache('baby_add_'.$user_id,1,10);
        $baby_data = [
            'user_id'   => $user_id,
            'name'      => $data['name'],
            'head_img'  => clearDomain($data['head_img']),
            'sex'       => $data['sex'],
            'birthday'  => strtotime($data['birthday']),
            'father_height' => round(abs(floatval($data['father_height'])),2),
            'mother_height' => round(abs(floatval($data['mother_height'])),2),
            'sup_height'    => round(abs(floatval($data['sup_height'])),2),
            'height'    => round(abs(floatval($data['height'])),2),
            'weight'    => round(abs(floatval($data['weight'])),2),
            'province_id'   => $data['province_id'],
            'city_id'       => $data['city_id'],
            'bone'          => $data['bone']??0,
            'create_time'   => time(),
        ];
        $last_date = $data['last_date']??'';
        $last_height = $data['last_height']??'';
        //遗传身高
        $baby_data['genetic_height'] = $data['sex'] == 1? bcdiv($baby_data['father_height']+$baby_data['mother_height']+13,2,2):bcdiv($baby_data['father_height']+$baby_data['mother_height']-13,2,2);
        //预测身高
        $baby_data['predicting_height'] = $data['sex'] == 1? bcmul(($baby_data['father_height']+$baby_data['mother_height'])/2,0.78,2) + 45.99:bcmul(($baby_data['father_height']+$baby_data['mother_height'])/2,0.75,2) + 37.85;
        $if_default = Db::name('baby')->where(['del'=>0,'user_id'=>$user_id,'is_default'=>1])->count();
        if(!$if_default){
            $baby_data['is_default'] = 1;
        }
        Db::startTrans();
        try {
            $baby_id  = Db::name('baby')->insertGetId($baby_data);

            //身高测评
            $check_data = [
                'id'    => $baby_id,
                'date'  => date('Y-m-d'),
                'height'    => $baby_data['height'],
                'weight'    => $baby_data['weight'],
                'last_date' => $last_date,
                'bone'      => $baby_data['bone'],
                'last_height'   => $last_height,
            ];
            $res = self::height_check($user_id,$check_data);
            if($res['status'] === false){
                Db::rollback();
                self::$error =  $res['msg'];
                return  false;
            }
            Db::commit();
            return [
                'id'    => $res['data'],
                'type'  => 1,
            ];
        } catch (\Exception $e) {
            Db::rollback();
            self::$error =  $e->getMessage();
            return  false;
        }
    }

    public static function height_check_before($user_id,$data)
    {
        $cache = cache('baby_height_check_'.$user_id);
        if($cache){
            self::$error =  '操作频繁';
            return  false;
        }
        cache('baby_height_check_'.$user_id,1,10);
        Db::startTrans();
        try {
            //身高测评
            $check_data = [
                'id'    => $data['id'],
                'date'  => $data['date'],
                'height'    => $data['height'],
                'weight'    => $data['weight'],
                'last_date' => $data['last_date']??'',
                'bone'      => $data['bone']??'',
                'last_height'   => $data['last_height']??'',
            ];
            $res = self::height_check($user_id,$check_data);
            if($res['status'] === false){
                Db::rollback();
                self::$error =  $res['msg'];
                return  false;
            }
            Db::commit();
            return [
                'id'    => $res['data'],
                'type'  => 1,
            ];
        } catch (\Exception $e) {
            Db::rollback();
            self::$error =  $e->getMessage();
            return  false;
        }

    }

    //测评
    public static function height_check($user_id,$data)
    {
        $res = ['data'=>'','msg'=>'','status'=>false];
        $now_date = strtotime(date('Y-m-d'));
        if(empty($data['id']) || empty($data['date']) || empty($data['height']) || empty($data['weight'])){
            $res['msg'] = '请填写完整';
            return $res;
        }
        $data_height = round($data['height'],2);
        $data_weight = round($data['weight'],2);
        $baby_data = Db::name('baby')->where('id',$data['id'])->where(['user_id'=>$user_id,'del'=>0])->lock()->find();
        if(!$baby_data){
            $res['msg'] = '宝贝错误';
            return $res;
        }
        $time = time();
        $post_time = strtotime($data['date']);
        if($now_date < $post_time){
            $res['msg'] = '测评日期错误';
            return $res;
        }
        if($post_time - $baby_data['birthday'] < 0){
            $res['msg'] = '测评日期错误';
            return $res;
        }
        $now_age_arr = now_age($baby_data['birthday'],$post_time,false);
        $sum_month = $now_age_arr['year'] * 12 + $now_age_arr['month'];

        if($now_age_arr['day'] == 0){
            $before_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','<',$sum_month)->order('month desc,height asc')->limit(7)->select();
            // $after_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','>',$sum_month)->order('month asc,height asc')->limit(7)->select();
        }else{
            $before_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','<',$sum_month)->order('month desc,height asc')->limit(7)->select();
            // $after_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','>=',$sum_month)->order('month asc,height asc')->limit(7)->select();
        }
        if(empty($before_moban)){// || empty($after_moban)
            $res['msg'] = '测评失败';
            return $res;
        }

        //同龄平均身高
        $arg_height = $before_moban[3]['height'];
        // $arg_height = round(($after_moban[3]['height'] - $before_moban[3]['height']) / 6 * ($sum_month - $before_moban[3]['month']) + $before_moban[3]['height'],2);
        //标准差
        $cha_height = bcdiv($data_height - $arg_height,5.3,2);
        //体重BMI
        $bmi = bcdiv($data_weight,($data_height/100)*($data_height/100),1);
        // $bmi_str = bmi_str($bmi);
        // 身高
        $height_str = height_str($data_height,$before_moban);
        //百分位
        $near_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','<=',$sum_month)->order('month desc,height asc')->limit(7)->select();
        //发育等级
        $grow_level = [
            'height'=>
                [
                    $near_moban[0]['height'],
                    $near_moban[2]['height'],
                    $near_moban[4]['height'],
                    $near_moban[6]['height'],
                ],
            'level_name' =>
            [
                0 => '下',
                1 => '中下',
                2 => '中',
                3 => '中上',
                4 => '上',
            ],
            'grow_level' => 0,
        ];
        foreach ($grow_level['height'] as $son)
        {
            if($son < $data_height){
                $grow_level['grow_level'] ++;
            }
        }
        $json_grow_level = json_encode($grow_level);
        foreach ($near_moban as $near_k => $near_v){
            if($near_v['height'] < $data_height){
                unset($near_moban[$near_k]);
            }
        }
        if(count($near_moban) < 2){
            $height_pot = 50;
            if($baby_data['sex']==1){
                $bf_predicting_height = 172.7;
            }else{
                $bf_predicting_height = 160.6;
            }
        }else{
            $near_moban = array_merge($near_moban);
            $height_pot = bcdiv($near_moban[1]['height'] -  $near_moban[0]['height'],(level_to_num($near_moban[1]['level']) - level_to_num($near_moban[0]['level'])),2) * 100;
            if($near_moban[0]['level'] >=4){
                $height_pot = 50 + $height_pot;
            }else{
                //身高百分位
                $height_pot = 50 - $height_pot;
            }
            //百分位预测身高
            $bf_predicting_height = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('level',$near_moban[0]['level'])->value('height');
        }

        //AI预测身高
        $ai_predicting_height = $baby_data['sex'] == 1? bcmul(($baby_data['father_height']+$baby_data['mother_height'])/2,0.78,2) + 45.99:bcmul(($baby_data['father_height']+$baby_data['mother_height'])/2,0.75,2) + 37.85;

        $weight_arr = array_column(weight_arr($baby_data['sex']),'age');
        $weight_key = 24;
        foreach ($weight_arr as $k=> $we){
            $mon = intval($we * 12);
            if($sum_month < $mon){
                $weight_key = $k;
                break;
            }
        }

        // $height_pot = bcdiv($near_moban[3]['height'] - $before_moban[3]['height'],25,2);
        // $cha_pot = bcdiv($arg_height - $data_height,$height_pot,2);
        // $now_pot = intval(50-$cha_pot);
        // var_dump($cha_height);die;
        $check_data = [
            'baby_id'   => $data['id'],
            'sex'       => $baby_data['sex'],
            'user_id'   => $user_id,
            'date'      => $post_time,
            'height'    => $data_height,
            'weight'    => $data_weight,
            'now_age_str'   => $now_age_arr,
            'arg_height'    => $arg_height,
            'cha_height'    => $cha_height,
            'bmi'           => $bmi,
            'bf_predicting_height' => $bf_predicting_height,
            'ai_predicting_height' => $ai_predicting_height,
            'height_pot'    => $height_pot,
            'weight_key'    => $weight_key,
            'moban_str'     => ['before_moban'=>$before_moban,'near_moban'=>$near_moban],//,'after_moban'=>$after_moban[3]
            'create_time'   => $time,
            'grow_level'    => $grow_level,
            'last_date'     => $data['last_date'],
            'last_height'   => $data['last_height'],
            'bone'          => $data['bone'],
            // 'cha_pot' => $cha_pot,
            // 'now_pot' => $now_pot,
            // 'bmi_str' => $bmi_str,
            // 'height_str' => $height_str,
        ];
        $result_name = 'result_'.$grow_level['grow_level'];
        $check_result = ConfigServer::get('baby_height', $result_name);
        $bone = $baby_data['bone'];
        if($check_data['bone']){
            $bone = $check_data['bone'];
        }
        $check_data = json_encode($check_data);
        $baby_data = json_encode($baby_data);
        $insert_id = Db::name('baby_check')->insertGetId([
            'user_id'       => $user_id,
            'baby_id'       => $data['id'],
            'type'          => 1,
            'check_data'    => $check_data,
            'check_date'    => $post_time,
            'create_time'   => $time,
            'check_result'  => $check_result,
            'baby_data'     => $baby_data,
        ]);
        Db::name('baby')->where('id',$data['id'])->update(['height'=>$data_height,'weight'=>$data_weight,'grow_level'=>$json_grow_level,'height_pot'=>$height_pot,'bone'=>$bone]);
        $res['status'] = true;
        $res['data'] = $insert_id;
        $res['msg'] = '成功';
        return $res;

    }

    public static function spleen_check($user_id,$data)
    {
        $baby_data = Db::name('baby')->where('id',$data['id'])->where(['user_id'=>$user_id,'del'=>0])->find();
        if(!$baby_data){
            self::$error =  '宝贝错误';
            return  false;
        }
        $post_time = strtotime(date('Y-m-d'));
        $num = 39;//count(spleen_ques());
        $ans = $data['ans']??[];
        if(empty($ans)){
            self::$error =  '请答题';
            return  false;
        }
        if(count($ans) != $num){
            self::$error =  '请回答所有题目';
            return  false;
        }
        $time = time();
        $total_num = array_sum($ans);
        $ans['total_num'] = $total_num;
        $check_data = json_encode($ans);
        $result_key = spleen_key($total_num);
        $result_name = 'result_'.$result_key;
        $check_result = ConfigServer::get('baby_spleen', $result_name);
        $baby_data = json_encode($baby_data);
        $insert_id = Db::name('baby_check')->insertGetId([
            'user_id'       => $user_id,
            'baby_id'       => $data['id'],
            'type'          => 2,
            'check_data'    => $check_data,
            'check_date'    => $post_time,
            'create_time'   => $time,
            'check_result'  => $check_result,
            'baby_data'     => $baby_data,
        ]);
        return [
            'id'    => $insert_id,
            'type'  => 2
        ];
    }

    public static function sleep_check($user_id,$data)
    {
        $baby_data = Db::name('baby')->where('id',$data['id'])->where(['user_id'=>$user_id,'del'=>0])->find();
        if(!$baby_data){
            self::$error =  '宝贝错误';
            return  false;
        }
        $post_time = strtotime(date('Y-m-d'));
        $num = 26;//count(sleep_ques());
        $ans = $data['ans']??[];
        if(empty($ans)){
            self::$error =  '请答题';
            return  false;
        }
        if(count($ans) != $num){
            self::$error =  '请回答所有题目';
            return  false;
        }
        $time = time();
        $res = sleep_check_com($ans);
        $check_result = json_encode($res);
        $check_data = json_encode($ans);
        $baby_data = json_encode($baby_data);
        $insert_id = Db::name('baby_check')->insertGetId([
            'user_id'       => $user_id,
            'baby_id'       => $data['id'],
            'type'          => 3,
            'check_data'    => $check_data,
            'check_date'    => $post_time,
            'create_time'   => $time,
            'check_result'  => $check_result,
            'baby_data'     => $baby_data,
        ]);
        return [
            'id'    => $insert_id,
            'type'  => 3
        ];
    }

    public static function index_baby($user_id)
    {
        $baby_data = Db::name('baby')->field('id,name,head_img,sex,birthday')->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->find();
        if(empty($baby_data)){
            return '';
        }
        $baby_data['head_img'] = addImgDomain($baby_data['head_img']);
        $baby_data['birthday_str'] = now_age($baby_data['birthday'],time(),true);
        return $baby_data;
    }

    public static function user_baby($user_id)
    {
        $baby_data = Db::name('baby')->field('id,name,head_img,sex,birthday,height,weight,height_pot,weight_pot,bone,grow_level')->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->find();
        if(empty($baby_data)){
            return '';
        }
        $baby_data['head_img'] = addImgDomain($baby_data['head_img']);
        $baby_data['birthday_str'] = now_age($baby_data['birthday'],time(),true);
        $baby_data['grow_level'] = json_decode($baby_data['grow_level'],true);
        $baby_data['count'] = Db::name('baby')->where(['user_id'=>$user_id,'del'=>0])->count();
        $baby_data['check_height_status'] = true;
        $baby_data['check_sleep_status'] = false;
        $baby_data['check_spleen_status'] = false;
        $baby_data['check_bone_status'] = false;
        $baby_data['check_sport_status'] = false;
        $baby_check_type_arr = Db::name('baby_check')->where('user_id',$user_id)->group('type')->column('type');
        if(in_array(2,$baby_check_type_arr)){
            $baby_data['check_spleen_status'] = true;
        }
        if(in_array(3,$baby_check_type_arr)){
            $baby_data['check_sleep_status'] = true;
        }
        if(in_array(4,$baby_check_type_arr)){
            $baby_data['check_bone_status'] = true;
        }
        if(in_array(5,$baby_check_type_arr)){
            $baby_data['check_sport_status'] = true;
        }
        return $baby_data;
    }

    public static function baby_list($user_id,$get,$page,$size){
        $where = [];
        $where[] = ['user_id','=',$user_id];
        $where[] = ['del','=',0];

        $baby = new Baby();

        //关键词搜索
        if(isset($get['keyword']) && $get['keyword']){
            $where[] = [ 'c.name', 'like', "%{$get['keyword']}%" ];
        }

        $baby_count = $baby->alias('c')
            ->where($where)
            ->count();

        $baby_list = $baby
            ->where($where)
            ->page($page,$size)
            ->order('id desc')
            ->field('id,is_default,name,head_img,sex,birthday')
            ->select();

        foreach ($baby_list as &$v){
            $v['head_img'] = addImgDomain($v['head_img']);
            $v['birthday_str'] = now_age($v['birthday'],time(),true);

        }
        $more = is_more($baby_count,$page,$size);  //是否有下一页
        $data = [
            'list'          => $baby_list,
            'page_no'       => $page,
            'page_size'     => $size,
            'count'         => $baby_count,
            'more'          => $more
        ];
        return $data;

    }

    public static function setDefaultBaby($user_id,$id)
    {
        Db::startTrans();
        try {
            $model = new Baby();
            $model->where('id',$id)->where(['user_id'=>$user_id,'del'=>0])->update(['is_default'=>1]);
            $model->where('user_id',$user_id)->where('id','<>',$id)->update(['is_default'=>0]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return  $e->getMessage();
        }
    }
}