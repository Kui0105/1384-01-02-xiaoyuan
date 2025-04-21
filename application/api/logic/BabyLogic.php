<?php


namespace app\api\logic;

use app\common\logic\LogicBase;
use app\common\model\Baby;
use think\Db;

class BabyLogic extends LogicBase{

    public static function add_baby($user_id,$data){
        $cache = cache('baby_add_'.$user_id);
        if($cache){
            return '操作频繁';
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
                'weight'    => $baby_data['weight']
            ];
            $res = self::check($user_id,$check_data);
            var_dump($res);die;
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    //测评
    public static function check($user_id,$data){

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
        if($post_time - $baby_data['birthday'] <= 0){
            $res['msg'] = '测评日期错误';
            return $res;
        }
        $now_age_arr = now_age($baby_data['birthday'],$post_time,false);
        $sum_month = $now_age_arr['year'] * 12 + $now_age_arr['month'];

        if($now_age_arr['day'] == 0){
            $before_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','<',$sum_month)->order('month desc,height asc')->limit(7)->select();
            $after_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','>',$sum_month)->order('month asc,height asc')->limit(7)->select();
        }else{
            $before_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','<',$sum_month)->order('month desc,height asc')->limit(7)->select();
            $after_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','>=',$sum_month)->order('month asc,height asc')->limit(7)->select();
        }
        if(empty($before_moban) || empty($after_moban)){
            $res['msg'] = '测评失败';
            return $res;
        }

        //同龄平均身高
        $arg_height = $before_moban[3]['height'];
        // $arg_height = round(($after_moban[3]['height'] - $before_moban[3]['height']) / 6 * ($sum_month - $before_moban[3]['month']) + $before_moban[3]['height'],2);
        //标准差
        $cha_height = bcdiv($data_height - $arg_height,5.3,2);
        //体重
        $bmi = bcdiv($data_weight,($data_height/100)*($data_height/100),1);
        $bmi_str = bmi_str($bmi);
        // 身高
        $height_str = height_str($data_height,$before_moban);
        //百分位
        $near_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','<=',$sum_month)->order('month desc,height asc')->limit(7)->select();
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
                $height_pot = 50 - $height_pot;
            }

            $bf_predicting_height = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('level',$near_moban[0]['level'])->value('height');

        }

        //预测身高
        $ai_predicting_height = $baby_data['sex'] == 1? bcmul(($baby_data['father_height']+$baby_data['mother_height'])/2,0.78,2) + 45.99:bcmul(($baby_data['father_height']+$baby_data['mother_height'])/2,0.75,2) + 37.85;


        var_dump($ai_predicting_height);die;
        var_dump($near_moban);die;
        // $height_pot = bcdiv($near_moban[3]['height'] - $before_moban[3]['height'],25,2);
        // $cha_pot = bcdiv($arg_height - $data_height,$height_pot,2);
        // $now_pot = intval(50-$cha_pot);
        // var_dump($cha_height);die;
        $check_data = [
            'baby_id' => $data['id'],
            'user_id' => $user_id,
            'date' => $post_time,
            'height' => $data_height,
            'weight' => $data_weight,
            'now_age_str' => json_encode($now_age_arr),
            'arg_height' => $arg_height,
            'cha_height' => $cha_height,
            'bmi' => $bmi,
            'bmi_str' => $bmi_str,
            'height_str' => $height_str,
            'height_pot' => $height_pot,
            'cha_pot' => $cha_pot,
            'now_pot' => $now_pot,
            'moban_str' => json_encode(['before_moban'=>$before_moban[3],'after_moban'=>$after_moban[3],'near_moban'=>$near_moban[3]]),
            'create_time' => $time,
        ];

        $insert_id = Db::name('baby_check')->insertGetId($check_data);
        Db::name('baby')->where('id',$data['id'])->update(['height'=>$data_height,'weight'=>$data_weight]);
        $res['status'] = true;
        $res['data'] = $insert_id;
        $res['msg'] = '成功';
        return $res;

    }
}