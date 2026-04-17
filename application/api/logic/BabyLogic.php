<?php


namespace app\api\logic;

use app\common\logic\AccountLogLogic;
use app\common\logic\LogicBase;
use app\common\model\Baby;
use app\common\model\BabyCheck;
use app\common\model\DevRegion;
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
            // var_dump($res);die;
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
            $before_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','<=',$sum_month)->order('month desc,height asc')->limit(7)->select();
            $after_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','>',$sum_month)->order('month asc,height asc')->limit(7)->select();
        }else{
            $before_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','<=',$sum_month)->order('month desc,height asc')->limit(7)->select();
            $after_moban = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month','>',$sum_month)->order('month asc,height asc')->limit(7)->select();
        }
        if(empty($before_moban) || empty($after_moban)){
            $res['msg'] = '测评失败';
            return $res;
        }

        $moban_18 = Db::name('height_moban')->where('sex',$baby_data['sex'])->where('month',216)->order('month desc,height asc')->limit(7)->select();
        //同龄平均身高
        // $arg_height = $before_moban[3]['height'];
        $arg_height = round(($after_moban[3]['height'] - $before_moban[3]['height']) / 6 * ($sum_month - $before_moban[3]['month']) + $before_moban[3]['height'],2);
        //标准差
        // $cha_height = bcdiv($data_height - $arg_height,5.3,2);
        //当前标准差
        $cha_height = bcdiv($before_moban[6]['height'] - $before_moban[0]['height'],3.72,2);
        //成年标准差
        $cha_18 = bcdiv($moban_18[6]['height'] - $moban_18[0]['height'],3.72,2);
        //体重BMI
        $bmi = bcdiv($data_weight,($data_height/100)*($data_height/100),2);
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
        $near_weight_moban = $near_moban;
        $near_height_moban = $near_moban;
        foreach ($near_weight_moban as $near_k => $near_v){
            if($near_v['weight'] < $data_weight){
                if(isset($near_weight_moban[$near_k-1])){
                    unset($near_weight_moban[$near_k-1]);
                }
            }
        }
        if(count($near_weight_moban) < 2){
            $weight_pot = 100;
        }else{
            $near_weight_moban = array_merge($near_weight_moban);
//             var_dump(level_to_num($near_weight_moban[0]['level']));
//             var_dump(level_to_num($near_weight_moban[1]['level']));
// var_dump($data_weight);
// var_dump($near_weight_moban[0]['weight']);
//             var_dump($near_weight_moban[1]['weight']);

            $weight_pot = bcadd(level_to_num($near_weight_moban[0]['level']),(level_to_num($near_weight_moban[1]['level']) - level_to_num($near_weight_moban[0]['level']))*(($data_weight -  $near_weight_moban[0]['weight'])/($near_weight_moban[1]['weight'] -  $near_weight_moban[0]['weight'])),2);
            // var_dump($weight_pot);
            //
            // var_dump($data_weight -  $near_weight_moban[0]['weight']);
            // var_dump($near_weight_moban[1]['weight'] -  $near_weight_moban[0]['weight']);
            // var_dump($near_weight_moban);die;

            // $weight_pot = bcdiv($near_weight_moban[1]['weight'] -  $near_weight_moban[0]['weight'],(level_to_num($near_weight_moban[1]['level']) - level_to_num($near_weight_moban[0]['level'])),4);
            //
            // $weight_pot = round(($data_weight - $near_weight_moban[0]['weight']) * $weight_pot * 10 ,1) ;
            // if($near_weight_moban[0]['level'] >=4){
            //     $weight_pot = 50 + $weight_pot;
            // }else{
            //     $weight_pot = level_to_num($near_weight_moban[0]['level']) + $weight_pot;
            // }
        }

        foreach ($near_moban as $near_k => $near_v){
            if($near_v['height'] < $data_height){
                if(isset($near_moban[$near_k-1])){
                    unset($near_moban[$near_k-1]);
                }

            }
        }
        if(count($near_moban) < 2){
            $height_pot = 100;
            // $weight_pot = 50;
            if($baby_data['sex']==1){
                $bf_predicting_height = 183.9;
            }else{
                $bf_predicting_height = 170.7;
            }
        }else{
            $near_moban = array_merge($near_moban);
            $height_pot = bcadd(level_to_num($near_moban[0]['level']),(level_to_num($near_moban[1]['level']) - level_to_num($near_moban[0]['level']))*(($data_height -  $near_moban[0]['height'])/($near_moban[1]['height'] -  $near_moban[0]['height'])),2);
            // $height_pot = bcdiv($data_height -  $near_moban[0]['height'],(level_to_num($near_moban[1]['level']) - level_to_num($near_moban[0]['level'])),2) * 10;
            // $height_pot = bcdiv($near_moban[1]['height'] -  $near_moban[0]['height'],(level_to_num($near_moban[1]['level']) - level_to_num($near_moban[0]['level'])),2) * 10;
            // $weight_pot = bcdiv($near_moban[1]['weight'] -  $near_moban[0]['weight'],(level_to_num($near_moban[1]['level']) - level_to_num($near_moban[0]['level'])),2) * 10;

            // $weight_pot = round(($data_height - $near_moban[0]['weight']) * $weight_pot / 10,2);
            // if($near_moban[0]['level'] >=4){
            //     $height_pot = 50 + $height_pot;
            //     // $weight_pot = 50 + $weight_pot;
            // }else{
            //     //身高百分位
            //     $height_pot = 50 - $height_pot;
            //     // $weight_pot = 50 - $weight_pot;
            // }
            //百分位预测身高
            $bf_predicting_height = Db::name('height_moban')->where('month',216)->where('sex',$baby_data['sex'])->where('level',$near_moban[1]['level'])->value('height');
            $bf_predicting_height -= ($near_moban[1]['height'] - $data_height);
        }

        if(in_array($now_age_arr,[0,6])){
            if($data_height <= $before_moban[0]['height']){
                $height_pot = 3;
                // $bf_predicting_height = $data_height;
            }else if($data_height >= $before_moban[6]['height']){
                $height_pot = 97;
                // $bf_predicting_height = $data_height;
            }else{
                foreach ($before_moban as $before_k => $before_v){
                    if($data_height == $before_v['height']){
                        $height_pot = level_to_num($before_v['level']);
                        // $bf_predicting_height = $data_height;
                        break;
                    }
                    if ($data_height < $before_v['height']) {
                        // 找到大百分位（第一个大于当前身高的值）
                        $big_percentile_index = $before_k;
                        // 小百分位是大百分位的前一个索引
                        $small_percentile_index = $big_percentile_index - 1;
                        
                        // 小百分位值和大百分位值
                        $small_percentile = level_to_num($before_moban[$small_percentile_index]['level']);
                        $big_percentile = level_to_num($before_v['level']);
                        
                        // 小百分位身高和大百分位身高
                        $small_height = $before_moban[$small_percentile_index]['height'];
                        $big_height = $before_v['height'];
                        
                        // 计算百分位
                        $height_pot = bcadd($small_percentile , ($data_height - $small_height) / ($big_height - $small_height) * ($big_percentile - $small_percentile),0);
                        // if($height_pot == $big_percentile){
                            // $bf_predicting_height = $big_height;
                        // }else{
                            // $bf_predicting_height = $small_height;
                        // }

                        break;
                    }
                }
            }
        }else{
            //1.年龄转换：整岁年龄+月龄÷12
            // (1)如目标年龄为4岁11个月，身高为107cm，年龄转换则为4+11÷12≈4.92岁，4.92岁低领为4.5，高龄为5岁
            $age_pot = bcadd($now_age_arr['year'],$now_age_arr['month']/12,2);
            if($now_age_arr['month'] > 6){
                $min_age_pot = bcadd($now_age_arr['year'],0.5,1);
                $max_age_pot = bcadd($now_age_arr['year'],1,1);
            }else{
                $min_age_pot = bcadd($now_age_arr['year'],0,1);
                $max_age_pot = bcadd($now_age_arr['year'],0.5,1);
            }

            //(1)插值比例=（目标年龄-低龄）÷（高龄-低龄）
            // ①（4.92-4.5）÷（5-4.5）=0.84
            $cha_pot = bcdiv(($age_pot-$min_age_pot),($max_age_pot-$min_age_pot),2);
            //(2)计算出当前年龄P3、P10、P25、P50、P75、P90、P97插值
            // ①插值=低龄身高+插值比例x（高龄身高-低龄身高）
            //②四舍五入保留一位小数
            $p3_pot  = bcadd($before_moban[0]['height'],$cha_pot*($after_moban[0]['height'] - $before_moban[0]['height']),1);
            $p10_pot = bcadd($before_moban[1]['height'],$cha_pot*($after_moban[1]['height'] - $before_moban[1]['height']),1);
            $p25_pot = bcadd($before_moban[2]['height'],$cha_pot*($after_moban[2]['height'] - $before_moban[2]['height']),1);
            $p50_pot = bcadd($before_moban[3]['height'],$cha_pot*($after_moban[3]['height'] - $before_moban[3]['height']),1);
            $p75_pot = bcadd($before_moban[4]['height'],$cha_pot*($after_moban[4]['height'] - $before_moban[4]['height']),1);
            $p90_pot = bcadd($before_moban[5]['height'],$cha_pot*($after_moban[5]['height'] - $before_moban[5]['height']),1);
            $p97_pot = bcadd($before_moban[6]['height'],$cha_pot*($after_moban[6]['height'] - $before_moban[6]['height']),1);

            $new_height_arr = [
                $p3_pot,
                $p10_pot,
                $p25_pot,
                $p50_pot,
                $p75_pot,
                $p90_pot,
                $p97_pot
            ];
            //3.百分位=小百分位+（当前身高-小百分位身高数值）÷（大百分位身高数值-小百分位身高数值）x（大百分位-小百分位）
            // (1)通过已知当前年龄插值取值
            // ①25+（107-106.8）÷（109.6-106.8）x（50-25）=26.7857=27%，则当前4岁11个月，身高107cm的女孩百分位为27%
            // (2)计算百分位值小于3%则直接为3%，大于97%则直接为97%
            if($data_height <= $p3_pot || $data_height >= $p97_pot || in_array($data_height,$new_height_arr)){
                // $bf_predicting_height = $data_height;
                if($data_height <= $p3_pot){
                    $height_pot = 3;
                }else if($data_height >= $p97_pot){
                    $height_pot = 97;
                }else{
                    // 根据在new_height_arr中的位置设置对应的height_pot值
                    $index = array_search($data_height, $new_height_arr);
                    switch($index){
                        case 1: // p10_pot
                            $height_pot = 10;
                            break;
                        case 2: // p25_pot
                            $height_pot = 25;
                            break;
                        case 3: // p50_pot
                            $height_pot = 50;
                            break;
                        case 4: // p75_pot
                            $height_pot = 75;
                            break;
                        case 5: // p90_pot
                            $height_pot = 90;
                            break;
                        default:
                            $height_pot = 123123;
                            break;
                    }
                }
            }else {
                foreach ($new_height_arr as $new_height_k => $new_height_v) {
                    if ($data_height < $new_height_v) {
                        // 找到大百分位（第一个大于当前身高的值）
                        $big_percentile_index = $new_height_k;
                        // 小百分位是大百分位的前一个索引
                        $small_percentile_index = $big_percentile_index - 1;

                        // 百分位序列
                        $percentiles = [3, 10, 25, 50, 75, 90, 97];

                        // 小百分位值和大百分位值
                        $small_percentile = $percentiles[$small_percentile_index];
                        $big_percentile   = $percentiles[$big_percentile_index];

                        // 小百分位身高和大百分位身高
                        $small_height = $new_height_arr[$small_percentile_index];
                        $big_height   = $new_height_v;

                        // 计算百分位
                        $height_pot = bcadd($small_percentile, ($data_height - $small_height) / ($big_height - $small_height) * ($big_percentile - $small_percentile), 0);
                        // $kkk = 'p'.$height_pot.'_pot';
                        // $bf_predicting_height = $$kkk;
                        // if($height_pot == $small_percentile){
                        //     $bf_predicting_height = $small_height;
                        // }else{
                        //     $bf_predicting_height = $big_height;
                        // }
                        break;
                    }
                }
            }
        }

        //百分预测身高
        $bf_predicting_height = round($bf_predicting_height,2);
        //AI预测身高
        $ai_predicting_height =  bcadd($moban_18[3]['height'] , ($data_height - $before_moban[3]['height'])/$cha_height * $cha_18,2);


        // $ai_predicting_height = $baby_data['sex'] == 1? bcmul(($baby_data['father_height']+$baby_data['mother_height'])/2,0.78,2) + 45.99:bcmul(($baby_data['father_height']+$baby_data['mother_height'])/2,0.75,2) + 37.85;
        // $ai_predicting_height = true;
        // if($baby_data['sex'] == 1){
        //     if($sum_month < 72 || $sum_month > 213){
        //         $ai_predicting_height = 0;
        //     }
        // }else{
        //     if($sum_month < 72 || $sum_month > 210){
        //         $ai_predicting_height = 0;
        //     }
        // }
        // if($ai_predicting_height === true){
        //     $ai_min_month = Db::name('height_new_moban')->where('sex', $baby_data['sex'])->where('month','<=',$sum_month)->max('month');
        //     $ai_max_month = Db::name('height_new_moban')->where('sex', $baby_data['sex'])->where('month','>',$sum_month)->min('month');
        //     $min_cha = abs(bcsub($sum_month,$ai_min_month));
        //     $max_cha = abs(bcsub($ai_max_month,$sum_month));
        //     if($min_cha < $max_cha){
        //         $ai_value = Db::name('height_new_moban')->where('sex', $baby_data['sex'])->where('month',$ai_min_month)->value('value');
        //     }else{
        //         $ai_value = Db::name('height_new_moban')->where('sex', $baby_data['sex'])->where('month',$ai_max_month)->value('value');
        //     }
        //     $ai_predicting_height = $data_height/$ai_value/100;
        // }
        // if($ai_predicting_height === true){
        //     $nearby_months = Db::name('height_new_moban')
        //         ->where('sex', $baby_data['sex'])
        //         ->where(function($query) use ($sum_month) {
        //             $query->where('month', '<=', $sum_month)
        //                 ->whereOr('month', '>', $sum_month);
        //         })
        //         ->order('month asc')
        //         ->select();
        //     $ai_value = null;
        //     if (!empty($nearby_months)) {
        //         $closest_month = null;
        //         $min_diff = PHP_INT_MAX;
        //         foreach ($nearby_months as $month_data) {
        //             $diff = abs($month_data['month'] - $sum_month);
        //             if ($diff < $min_diff) {
        //                 $min_diff = $diff;
        //                 $closest_month = $month_data;
        //             }
        //         }
        //         if ($closest_month) {
        //             $ai_value = $closest_month['value'];
        //         }
        //     }
        //     if ($ai_value !== null) {
        //         // $ai_predicting_height = $data_height / $ai_value / 100;
        //         $ai_predicting_height = bcdiv($data_height,$ai_value/100,2);
        //     } else {
        //         $ai_predicting_height = 0;
        //     }
        // }
//         var_dump($sum_month);
//         var_dump($ai_value);
// var_dump($ai_predicting_height);die;


        //AI写的
        // AI预测身高算法 - 按照新需求重新实现
        $genetic_weight = 0;
        $current_height_weight = 0;

        // 根据性别和年龄确定权重
        $age_in_years = $now_age_arr['year'] + $now_age_arr['month'] / 12;

        if ($baby_data['sex'] == 2) { // 女孩
            if ($age_in_years < 3) {
                $genetic_weight = 0.7;
                $current_height_weight = 0.3;
            } elseif ($age_in_years < 5) {
                $genetic_weight = 0.6;
                $current_height_weight = 0.4;
            } elseif ($age_in_years < 8) {
                $genetic_weight = 0.5;
                $current_height_weight = 0.5;
            } elseif ($age_in_years < 10) {
                $genetic_weight = 0.4;
                $current_height_weight = 0.6;
            } elseif ($age_in_years < 12) {
                $genetic_weight = 0.3;
                $current_height_weight = 0.7;
            } elseif ($age_in_years < 14) {
                $genetic_weight = 0.2;
                $current_height_weight = 0.8;
            } elseif ($age_in_years < 16) {
                $genetic_weight = 0.1;
                $current_height_weight = 0.9;
            } else { // 16-18
                $genetic_weight = 0.05;
                $current_height_weight = 0.95;
            }
        } else { // 男孩
            if ($age_in_years < 3) {
                $genetic_weight = 0.7;
                $current_height_weight = 0.3;
            } elseif ($age_in_years < 10) {
                $genetic_weight = 0.5;
                $current_height_weight = 0.5;
            } elseif ($age_in_years < 14) {
                $genetic_weight = 0.3;
                $current_height_weight = 0.7;
            } else { // 14-18
                $genetic_weight = 0.1;
                $current_height_weight = 0.9;
            }
        }

        // 获取18岁时的P50身高（第4个模板数据，索引为3）
        // $p50_18_height = $moban_18[3]['height'];

        $height_percentile_index = 3; // 默认使用P50对应的索引

        // 根据孩子当前身高在同龄人中的百分位确定对应18岁的P值
        if ($height_pot <= 3) {
            // P3水平
            $height_percentile_index = 0;
        } elseif ($height_pot <= 15) {
            // P15水平
            $height_percentile_index = 1;
        } elseif ($height_pot <= 25) {
            // P25水平
            $height_percentile_index = 2;
        } elseif ($height_pot <= 75) {
            // P50水平（中间范围）
            $height_percentile_index = 3;
        } elseif ($height_pot <= 85) {
            // P75水平
            $height_percentile_index = 4;
        } elseif ($height_pot <= 97) {
            // P85水平
            $height_percentile_index = 5;
        } else {
            // P97水平
            $height_percentile_index = 6;
        }

        $p50_18_height = $moban_18[$height_percentile_index]['height'];

        // 基础预测值 = 遗传身高 × 遗传权重 + 18岁P50身高 × 当前身高权重
        $base_prediction = $baby_data['genetic_height'] * $genetic_weight + $p50_18_height * $current_height_weight;
// var_dump('基础预测值:'.$base_prediction.'遗传身高:'.$baby_data['genetic_height'].'遗传权重:'.$genetic_weight.'18岁P50身高:'.$p50_18_height.'当前身高权重:'.$current_height_weight);
        // BMI修正值
        $bmi_correction = 0;
        $weight_pot_diff = $weight_pot - $height_pot;
        if ($weight_pot_diff >= 20) {
            $bmi_correction = -0.5; // 超重
        } elseif ($weight_pot_diff <= -20) {
            $bmi_correction = 0.5; // 偏瘦
        }

        // 骨龄修正值
        $bone_age_correction = 0;
        if ($data['bone']) { // 如果提供了骨龄数据
            // 这里需要根据骨龄计算逻辑来计算骨龄与实际年龄的差值
            // 假设$data['bone']是骨龄时间戳，我们需要与实际年龄比较
            $actual_age_months = $now_age_arr['year'] * 12 + $now_age_arr['month'];

            /// 处理骨龄数据（可能是岁数也可能是月份）
            $bone_input = $data['bone'];
            $bone_age_months = 0;
            if (is_numeric($bone_input)) {
                // 如果是纯数字，判断是否超过18（可能是岁数）还是超过216（18岁=216个月）
                if ($bone_input <= 18) {
                    // 假设是岁数
                    $bone_age_months = $bone_input * 12;
                } else {
                    // 假设是月数
                    $bone_age_months = $bone_input;
                }
            }

            $bone_age_difference = $bone_age_months - $actual_age_months;

            if ($bone_age_difference > 12) { // 骨龄>实际年龄+1岁
                $bone_age_correction = max(-3, -($bone_age_difference - 12));
            } elseif ($bone_age_difference < -12) { // 骨龄<实际年龄-1岁
                $bone_age_correction = min(3, abs($bone_age_difference + 12));
            }
            // 否则骨龄修正值为0
        }

        // 最终AI预测身高 = 基础预测值 + BMI修正值 + 骨龄修正值
        $ai_predicting_height = $base_prediction + $bmi_correction + $bone_age_correction;
// var_dump('最终AI预测身高:'.$ai_predicting_height.'基础预测值:'.$base_prediction.'BMI修正值:'.$bmi_correction.'骨龄修正值:'.$bone_age_correction);die;
        // 如果当前身高低于模板值中第一个的身高，则$ai_predicting_height减去（模板值第一个身高-当前身高）
        if ($data_height < $near_height_moban[0]['height']) {
            $ai_predicting_height = $ai_predicting_height - ($near_height_moban[0]['height'] - $data_height);
        }


        // 四舍五入保留1位小数
        $ai_predicting_height = round($ai_predicting_height, 1);
        // AI预测身高

        // 小元AI预测身高约束规则
        $age_in_years = $now_age_arr['year'] + $now_age_arr['month'] / 12;
        if ($baby_data['sex'] == 2) { // 女孩
            if ($age_in_years >= 16) {
                $ai_predicting_height = $data_height; // 16岁及以上取当前身高
            } elseif ($age_in_years >= 15) {
                $ai_predicting_height = $data_height + 0.5; // 15岁当前身高+0.5cm
            } elseif ($age_in_years >= 14) {
                $ai_predicting_height = $data_height + 1; // 14岁当前身高+1cm
            }
        } else { // 男孩
            if ($age_in_years >= 18) {
                $ai_predicting_height = $data_height; // 18岁及以上取当前身高
            } elseif ($age_in_years >= 17) {
                $ai_predicting_height = $data_height + 0.5; // 17岁当前身高+0.5cm
            } elseif ($age_in_years >= 16) {
                $ai_predicting_height = $data_height + 1; // 16岁当前身高+1cm
            }
        }

        //新增ai预测算法








        $weight_arr = array_column(weight_arr($baby_data['sex']),'age');
        $weight_key = 24;
        foreach ($weight_arr as $k=> $we){
            $mon = intval($we * 12);
            if($sum_month <= $mon){
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
            'bf_predicting_height' => round($bf_predicting_height,2),
            'ai_predicting_height' => round($ai_predicting_height,2),
            'height_pot'    => round($height_pot),
            'weight_pot'    => round($weight_pot),
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
        $max_check_date = Db::name('baby_check')->where('baby_id',$data['id'])->where('type',1)->max('check_date');
        if($post_time >= $max_check_date){
            Db::name('baby')->where('id',$data['id'])->update(['height'=>$data_height,'weight'=>$data_weight,'grow_level'=>$json_grow_level,'height_pot'=>$height_pot,'weight_pot'=>$weight_pot,'bone'=>$bone]);
        }

        AccountLogLogic::sendPoint($user_id, 'height');
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

    public static function bone_check($user_id,$pdf)
    {
        $baby_data = Db::name('baby')->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->find();
        if(!$baby_data){
            self::$error =  '宝贝错误';
            return  false;
        }
        $post_time = strtotime(date('Y-m-d'));
        $pdf = implode(',',clearDomain(explode(',',$pdf)));
        $time = time();
        $baby_id = $baby_data['id'];
        $baby_data = json_encode($baby_data);
        $check_data  = json_encode(['url'=>$pdf]);
        $insert_id = Db::name('baby_check')->insertGetId([
            'user_id'       => $user_id,
            'baby_id'       => $baby_id,
            'type'          => 4,
            'check_data'    => $check_data,
            'check_date'    => $post_time,
            'create_time'   => $time,
            'check_result'  => '',
            'baby_data'     => $baby_data,
        ]);
        return [
            'id'    => $insert_id,
            'type'  => 4
        ];
    }

    public static function index_baby($user_id)
    {
        $baby_data = Db::name('baby')->field('id,name,head_img,sex,birthday,height,weight')->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->find();
        if(empty($baby_data)){
            return '';
        }
        $baby_data['head_img'] = addImgDomain($baby_data['head_img']);
        $baby_data['birthday_str'] = now_age($baby_data['birthday'],time(),true);
        return $baby_data;
    }

    public static function user_baby($user_id)
    {
        $baby_data = Db::name('baby')->field('id,name,head_img,sex,birthday,height,weight,height_pot,weight_pot,bone,grow_level,sup_height,genetic_height')->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->find();
        if(empty($baby_data)){
            return '';
        }
        $baby_data['height_pot'] = ($baby_data['height_pot'] > 100 || $baby_data['height_pot'] < 0 ) ? 0 : $baby_data['height_pot'];
        $baby_data['weight_pot'] = ($baby_data['weight_pot'] > 100 || $baby_data['weight_pot'] < 0 ) ? 0 : $baby_data['weight_pot'];
        $baby_data['height'] = bcdiv(round($baby_data['height'],1),1,1);
        $baby_data['weight'] = bcdiv(round($baby_data['weight'],1),1,1);
        $baby_data['head_img'] = addImgDomain($baby_data['head_img']);
        $baby_data['birthday_str'] = now_age($baby_data['birthday'],time(),true);
        // $baby_data['grow_level'] = json_decode($baby_data['grow_level'],true);
        $grow_level = json_decode($baby_data['grow_level'],true);
        // if(!empty($grow_level)){
        //     $grow_level['grow_level'] = 4 - $grow_level['grow_level'];
        //     $grow_level['height'] = array_reverse($grow_level['height']);
        //     $grow_level['level_name'] = array_reverse($grow_level['level_name']);
        // }
        $baby_data['grow_level'] = $grow_level;
        $baby_data['count'] = Db::name('baby')->where(['user_id'=>$user_id,'del'=>0])->count();
        $baby_data['check_height_status'] = false;
        $baby_data['check_sleep_status'] = false;
        $baby_data['check_spleen_status'] = false;
        $baby_data['check_bone_status'] = false;
        $baby_data['check_sport_status'] = false;
        $baby_check_type_arr = Db::name('baby_check')->where('baby_id',$baby_data['id'])->where('is_del',0)->group('type')->column('type');
        if(in_array(1,$baby_check_type_arr)){
            $baby_data['check_height_status'] = true;
        }
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
            $res = $model->where('id',$id)->where(['user_id'=>$user_id,'del'=>0])->update(['is_default'=>1]);
            if(!$res){
                return '失败';
            }
            $model->where('user_id',$user_id)->where('id','<>',$id)->update(['is_default'=>0]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return  $e->getMessage();
        }
    }

    public static function baby_info($user_id)
    {
        $baby_data = Db::name('baby')->field('id,name,head_img,sex,birthday,father_height,mother_height,sup_height,province_id,city_id')->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->find();
        if(empty($baby_data)){
            return '';
        }
        $baby_data['age'] = now_age($baby_data['birthday'],time());
        $baby_data['head_img'] = addImgDomain($baby_data['head_img']);
        $baby_data['birthday'] = date('Y-m-d',$baby_data['birthday']);
        $baby_data['province'] = DevRegion::getArea($baby_data['province_id']);
        $baby_data['city'] = DevRegion::getArea($baby_data['city_id']);
        return $baby_data;
    }

    public static function edit_baby($user_id,$data)
    {
        $model = new Baby();
        $baby = $model->where(['id'=>$data['id'],'user_id'=>$user_id,'del'=>0])->find();
        if(!$baby){
            self::$error = '宝宝不存在或已被删除';
            return false;
        }
        $cache = cache('baby_edit_'.$user_id);
        if($cache){
            self::$error = '操作频繁';
            return  false;
        }
        cache('baby_edit_'.$user_id,1,10);
        $time = time();
        $baby_data = [
            'user_id'   => $user_id,
            'name'      => $data['name'],
            'head_img'  => clearDomain($data['head_img']),
            'sex'       => $data['sex'],
            'birthday'  => strtotime($data['birthday']),
            'father_height' => round(abs(floatval($data['father_height'])),2),
            'mother_height' => round(abs(floatval($data['mother_height'])),2),
            'sup_height'    => round(abs(floatval($data['sup_height'])),2),
            'province_id'   => $data['province_id'],
            'city_id'       => $data['city_id'],
        ];
        $baby->name     = $data['name'];
        $baby->head_img = clearDomain($data['head_img']);
        $baby->sex      = $data['sex'];
        $baby->birthday = strtotime($data['birthday']);
        $baby->father_height = round(abs(floatval($data['father_height'])),2);
        $baby->mother_height = round(abs(floatval($data['mother_height'])),2);
        $baby->sup_height   = round(abs(floatval($data['sup_height'])),2);
        $baby->province_id  = $data['province_id'];
        $baby->city_id      = $data['city_id'];
        //遗传身高
        $baby->genetic_height = $data['sex'] == 1? bcdiv($baby_data['father_height']+$baby_data['mother_height']+13,2,2):bcdiv($baby_data['father_height']+$baby_data['mother_height']-13,2,2);
        //预测身高
        $baby->predicting_height = $data['sex'] == 1? bcmul(($baby_data['father_height']+$baby_data['mother_height'])/2,0.78,2) + 45.99:bcmul(($baby_data['father_height']+$baby_data['mother_height'])/2,0.75,2) + 37.85;
        return $baby->save();
    }

    public static function history_check_list($user_id,$get,$page,$size)
    {
        $baby_model = new Baby();
        $baby_id = $baby_model->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->value('id');
        if(!$baby_id){
            self::$error = '暂无默认宝宝';
            return  false;
        }
        $model = new BabyCheck();

        $where = [
            ['baby_id','=',$baby_id]
        ];

        $list = $model->where($where)
            ->field("id,check_date,create_time,type,baby_data")
            ->order('check_date desc,id desc')
            ->page($page,$size)
            ->select()->toArray();

        $count = $model->where($where)
            ->order('check_date desc,id desc')
            ->count();

        $grouped_logs = [];
        foreach ($list as $log) {
            $log['time_str'] = date('H:i',$log['create_time']);
            $log['name']     = json_decode($log['baby_data'],true)['name'];
            $log['type_str']   = Baby::get_type_str($log['type']);
            unset($log['baby_data']);
            unset($log['create_time']);
            $date = date('Y-m-d', $log['check_date']);
            unset($log['check_date']);
            switch ($log['type']){
                case 1:
                    $log['img'] = addImgDomain('api_img/1.png');
                    break;
                case 2:
                    $log['img'] = addImgDomain('api_img/sleep.png');
                    break;
                case 3:
                    $log['img'] = addImgDomain('api_img/3.png');
                    break;
                case 4:
                    $log['img'] = addImgDomain('api_img/sleep.png');
                    break;
                case 5:
                    $log['img'] = addImgDomain('api_img/5.png');
                    break;
                case 6:
                    $log['img'] = addImgDomain('api_img/6.png');
                    break;
                default:
                    $log['img'] = addImgDomain('api_img/sleep.png');
                    break;
            }
            if (!isset($grouped_logs[$date])) {
                $grouped_logs[$date] = [];
            }
            $grouped_logs[$date][] = $log;
        }

        // $date_list = $model->where($where)
        //     ->field("FROM_UNIXTIME(check_date, '%Y-%m-%d') as date")
        //     ->group('date')
        //     ->order('date desc')
        //     ->page($page,$size)
        //     ->select()->toArray();
        //
        // $count = $model->where($where)
        //     ->field("FROM_UNIXTIME(check_date, '%Y-%m-%d') as date")
        //     ->group('date')
        //     ->order('date desc')
        //     ->count();
        //
        // foreach ($date_list as &$date) {
        //     $where[] = ['check_date','=',strtotime($date['date'])];
        //     $log = $model->where($where)->field('id,create_time')->order('id desc')->select()->toArray();
        //     foreach ($log as &$son) {
        //         $son['create_time'] = date('H:i:s',strtotime($son['create_time']));
        //     }
        //     $date['log'] = $log;
        // }

        $data = [
            'lists' => $grouped_logs,
            'page'  => $page,
            'size'  => $size,
            'count' => $count,
            'more'  => is_more($count, $page, $size)
        ];
        return $data;
    }

    public static function check_result($user_id,$id)//
    {
        $model = new BabyCheck();
        if($user_id){
            $check_result = $model->where(['id'=>$id,'user_id'=>$user_id])->findOrEmpty()->toArray();
        }else{
            $check_result = $model->where(['id'=>$id])->findOrEmpty()->toArray();//,'user_id'=>$user_id
        }
        if(empty($check_result)){
            self::$error = '测评不存在';
            return false;
        }
        $return_data = $model::get_check_baby($check_result);
        switch ($check_result['type']){
            case 1://身高
                $result_data = json_decode($check_result['check_data'],true);
                switch ($result_data['grow_level']['grow_level']){
                    case 0:
                        $return_data['height_str'] = '身高矮小';//（身高百分位低于3%）
                        break;
                    case 1:
                        $return_data['height_str'] = '身高偏矮';//（身高百分位低于25%）
                        break;
                    case 2:
                        $return_data['height_str'] = '身高正常';
                        break;
                    case 3:
                        $return_data['height_str'] = '身高偏高';//（身高百分位高于75%）
                        break;
                    case 4:
                        $return_data['height_str'] = '身高超高';//（身高百分位高于97%）
                        break;
                }
                $grow_level = $result_data['grow_level'];
                // if(!empty($grow_level)){
                //     $grow_level['grow_level'] = 4 - $grow_level['grow_level'];
                //     $grow_level['height'] = array_reverse($grow_level['height']);
                //     $grow_level['level_name'] = array_reverse($grow_level['level_name']);
                // }
                $return_data['grow_level'] = $grow_level;

                $return_data['height'] = floatval($result_data['height']);//身高
                $return_data['weight'] = $result_data['weight'];//体重
                $return_data['bone'] = $result_data['bone']??'';//骨龄
                $return_data['arg_height'] = floatval($result_data['arg_height']);//全国平均身高
                $return_data['arg_height_str'] = (($result_data['arg_height'] > $result_data['height'])?'矮':'高').abs(round($result_data['arg_height'] - $result_data['height'],2)).'cm';//全国平均身高字符串
                $return_data['height_pot'] = $result_data['height_pot'];//身高百分位
                $return_data['cha_height'] = $result_data['cha_height'];//标准差
                $return_data['ai_predicting_height'] = round($result_data['ai_predicting_height'],2);//AI测评身高
                $return_data['ai_predicting_height_str'] = (($return_data['sup_height'] > $result_data['ai_predicting_height'])?'矮':'高').abs(round($result_data['ai_predicting_height'] - $return_data['sup_height'],2)).'cm';//AI测评身高字符串
                $return_data['bf_predicting_height'] = round($result_data['bf_predicting_height'],2);//百分位测评身高
                $return_data['bf_predicting_height_str'] = (($return_data['sup_height'] > $result_data['bf_predicting_height'])?'矮':'高').abs(round($result_data['bf_predicting_height'] - $return_data['sup_height'],2)).'cm';//百分位测评身高字符串
                $return_data['arg_18_height'] = $return_data['sex']==1?'172.7':'160.6';//平均成年身高
                $return_data['bmi'] = $result_data['bmi'];//BMI
                $return_data['weight_key'] = $result_data['weight_key'];//体重区间
                $return_data['weight_arr'] = weight_arr($return_data['sex']);//体重数组

                $return_data['check_msg'] = $check_result['check_result'];//测评建议
                //1-18岁 身高图
                $int_month_arr = [0,12,24,36,48,60,72,84,96,108,120,132,144,156,168,180,192,204,216];
                $full_data = Db::name('height_moban')->where('sex',$return_data['sex'])->where('month','in',$int_month_arr)->field('weight,height,level,month')->order('level asc,id asc')->select();
                $full_height_by_level = $full_height_by_level_copy = [];
                $full_weight_by_level = $full_weight_by_level_copy = [];
                foreach ($full_data as $item) {
                    $key = $item['level'];
                    if (!isset($full_height_by_level[$key])) {
                        $full_height_by_level[$key] = [];
                        $n=0;
                    }
                    $full_height_by_level[$key][] = [$n,(float)$item['height']];


                    if(!isset($full_height_by_level_copy[$key])){
                        $full_height_by_level_copy[$key] = [];
                    }
                    $full_height_by_level_copy[$key][] = (float)$item['height'];
                    // $full_height_by_level[] = [
                    //     $key,$item['height']
                    // ];

                    if (!isset($full_weight_by_level[$key])) {
                        $full_weight_by_level[$key] = [];
                    }
                    $full_weight_by_level[$key][] = [$n,(float)$item['weight']];
                    $n++;
                    if(!isset($full_weight_by_level_copy[$key])){
                        $full_weight_by_level_copy[$key] = [];
                    }
                    $full_weight_by_level_copy[$key][] = (float)$item['weight'];
                }

                $return_data['full_height_by_level'] = $full_height_by_level;
                $return_data['full_height_by_level_copy'] = $full_height_by_level_copy;
                $return_data['full_height_x'] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18];
                $return_data['full_weight_x'] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18];
                $return_data['full_weight_by_level'] = $full_weight_by_level;
                $return_data['full_weight_by_level_copy'] = $full_weight_by_level_copy;

                $now_age_arr = now_age($return_data['birthday'],$check_result['check_date'],false);

                $all_check = Db::name('baby_check')->where(['baby_id'=>$check_result['baby_id'],'type'=>1])->group('check_date')->order('check_date asc')->field('check_date,baby_data,check_data')->select();
                $now_full_height_x = $now_full_weight_x = [];
                foreach ($all_check as $item){
                    $check_data     = json_decode($item['check_data'],true);
                    $baby_data = json_decode($item['baby_data'],true);
                    $now_age_str = now_age($baby_data['birthday'],$item['check_date'],false,true);
                    $sstr = now_age($baby_data['birthday'],$item['check_date']);
                    $now_full_height_x[] = [
                        (float)$now_age_str,(float)$check_data['height'],$sstr
                    ];
                    $now_full_weight_x[] = [
                        (float)$now_age_str,(float)$check_data['weight'],$sstr
                    ];
                }
                // $now_full_height_x = array_fill(0, count($return_data['full_height_x']), null);
                // if (isset($now_age_arr['year']) && isset($return_data['height'])) {
                //     $now_full_height_x[$now_age_arr['year']] = $return_data['height'];
                // }
                $return_data['now_full_height_x'] = $now_full_height_x;
                $return_data['now_full_weight_x'] = $now_full_weight_x;

                $now_year = $now_age_arr['year'];
                //1-18岁 体重图
                // $weight_full_data = Db::name('height_moban')->where('sex',$return_data['sex'])->where('month','in',$int_month_arr)->field('weight,level,month')->order('id asc')->select();
                // $full_weight_by_level = [];
                // foreach ($weight_full_data as $item) {
                //     $key = $item['level'];
                //     if (!isset($full_weight_by_level[$key])) {
                //         $full_weight_by_level[$key] = [];
                //     }
                //     $full_weight_by_level[$key][] = $item['weight'];
                // }
                // $return_data['full_weight_by_level'] = $full_weight_by_level;
                // $return_data['full_weight_x'] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18];
                //
                // $now_full_weight_x = array_fill(0, count($return_data['full_weight_x']), null);
                // if (isset($now_age_arr['year']) && isset($return_data['weight'])) {
                //     $now_full_weight_x[$now_age_arr['year']] = $return_data['weight'];
                // }
                // $return_data['now_full_weight_x'] = $now_full_weight_x;

                //2年区间图
                if($now_year<=0){
                    $start_year = 0;
                    $end_year = 1;
                }else if($now_year < 18){
                    $start_year = $now_year;
                    $end_year = $now_year+1;
                }else{
                    $start_year = 17;
                    $end_year = 18;
                }
                $area_height_str = $start_year.'~'.$end_year;
                $return_data['area_height_str'] = $area_height_str;

                $area_int_month_arr = [$start_year*12,$end_year*12];
                $area_full_data = Db::name('height_moban')->where('sex',$return_data['sex'])->where('month','in',$area_int_month_arr)->field('height,level,month')->order('level asc,id asc')->select();
                $area_full_height_by_level = $area_full_height_by_level_copy = [];
                foreach ($area_full_data as $item) {
                    $key = $item['level'];
                    if (!isset($area_full_height_by_level_copy[$key])) {
                        $area_full_height_by_level_copy[$key] = [];

                    }
                    $area_full_height_by_level_copy[$key][] = (float)$item['height'];

                    if (!isset($area_full_height_by_level[$key])) {
                        $area_full_height_by_level[$key] = [];
                        $n = 0;
                    }
                    if($n==1){
                        $n = 12;
                    }
                    $area_full_height_by_level[$key][] = [$n,(float)$item['height']];
                    $n++;
                }

               // foreach ($area_full_height_by_level as &$son){
               //     $secondElement = $son[1];
               //     array_pop($son);
               //     for ($i = 1; $i < 12; $i++) {
               //         $son[] = [$i, null];
               //     }
               //     $son[] = $secondElement;
               //     usort($son, function($a, $b) {
               //         return $a[0] - $b[0];
               //     });
               // }

                // $insertArr = [null,null,null,null,null,null,null,null,null,null];
                // foreach ($area_full_height_by_level_copy as &$al){
                //     array_splice($al, 1, 0, $insertArr);
                // }
                $return_data['area_full_height_by_level'] = $area_full_height_by_level;
                $return_data['area_full_height_by_level_copy'] = $area_full_height_by_level_copy;

                $return_data['area_full_height_x'] = [0,1,2,3,4,5,6,7,8,9,10,11,12];

                // 计算当前年龄和下一岁的时间范围
                $start_time = strtotime(date('Y-m-d', $return_data['birthday']) . ' +' . $now_year . ' years');
                $end_time = strtotime(date('Y-m-d', $return_data['birthday']) . ' +' . ($now_year + 1) . ' years');


                $now_area_check = Db::name('baby_check')->where(['baby_id'=>$check_result['baby_id'],'type'=>1])->whereBetween('check_date', [ $start_time,$end_time])->group('check_date')->order('check_date asc')->field('check_date,baby_data')->select();
                $area_now_full_height_x = [];
                foreach ($now_area_check as $item){
                    $baby_data = json_decode($item['baby_data'],true);
                    $now_age_str = now_age($baby_data['birthday'],$item['check_date'],false,true,true);
                    $sstr = now_age($baby_data['birthday'],$item['check_date']);
                    $area_now_full_height_x[] = [
                        (float)$now_age_str,(float)$baby_data['height'],$sstr
                    ];
                }

                // $area_now_full_height_x = array_fill(0, count($return_data['area_full_height_x']), null);
                // if (isset($now_age_arr['month']) && isset($return_data['height'])) {
                //     $area_now_full_height_x[$now_age_arr['month']] = $return_data['height'];
                // }
                $return_data['area_now_full_height_x'] = $area_now_full_height_x;
                break;
            case 2://脾胃
                $check_data = json_decode($check_result['check_data'],true);
                $return_data['check_num'] = $check_data['total_num'];
                $return_data['check_msg'] = $check_result['check_result'];
                $return_data['check_key'] = spleen_key($check_data['total_num']);
                break;
            case 3://睡眠
                $result_data = json_decode($check_result['check_result'],true);
                foreach ($result_data as $k => &$r){
                    if(!isset($r['num'])){
                        $r['num'] = 0;
                    }
                    // $r['status'] = false;
                    // if($r['num'] > 39){
                    //     $r['status'] = true;
                    // }
                    switch ($k){
                        case 0:
                            $r['total_num'] = 130;
                            $r['img_num'] = round($r['num']/$r['total_num'],2);
                            break;
                        case 1:
                            $r['total_num'] = 5;
                            $r['img_num'] = round($r['num']/$r['total_num'],2);
                            break;
                        case 2:
                            $r['total_num'] = 35;
                            $r['img_num'] = round($r['num']/$r['total_num'],2);
                            break;
                        case 3:
                            $r['total_num'] = 95;
                            $r['img_num'] = round($r['num']/$r['total_num'],2);
                            break;
                        case 4:
                            $r['total_num'] = 15;
                            $r['img_num'] = round($r['num']/$r['total_num'],2);
                            break;
                        case 5:
                            $r['total_num'] = 15;
                            $r['img_num'] = round($r['num']/$r['total_num'],2);
                            break;
                        case 6:
                            $r['total_num'] = 30;
                            $r['img_num'] = round($r['num']/$r['total_num'],2);
                            break;
                        case 7:
                            $r['total_num'] = 25;
                            $r['img_num'] = round($r['num']/$r['total_num'],2);
                            break;
                        case 8:
                            $r['total_num'] = 10;
                            $r['img_num'] = round($r['num']/$r['total_num'],2);
                            break;
                    }
                }
                $return_data['total_num'] = $result_data[0]['num'];
                $return_data['total_total_num'] = $result_data[0]['total_num'];
                $return_data['result_data'] = $result_data;
                break;
            case 4://骨龄
                $return_data = addImgDomain($check_result['check_result']);
                break;
            case 5://运动
                $return_data['check_result'] = json_decode($check_result['check_result'],true);
                $grade = json_decode($check_result['check_data'],true)['grade'];
                $sex = json_decode($check_result['baby_data'],true)['sex'];
                $ques_arr = sport_grade_to_select($grade,$sex);
                $danwei_k_arr = array_column($ques_arr,'dw','key');
                foreach ($return_data['check_result'] as &$r){
                    if($r['key'] != 'bmi'){
                        $r['dw'] = "(".$danwei_k_arr[$r['key']].")";
                    }else{
                        $r['dw'] = "";
                    }
                }
                break;
            case 6://营养
                $return_data['check_result'] = json_decode($check_result['check_result'],true);
                foreach ($return_data['check_result']['check_result'] as &$item) {
                    if (isset($item['num'])) {
                        $item['num'] = round($item['num'], 2);
                    }
                }
                // var_dump($return_data['check_result']['check_result']);die;
                break;
        }
        return $return_data;
    }

    public static function height_log_near($user_id,$get)
    {
        $baby_model = new Baby();
        $baby_id = $baby_model->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->value('id');
        if(!$baby_id){
            self::$error = '暂无默认宝宝';
            return  false;
        }
        $model = new BabyCheck();
        $id = $model->where(['baby_id'=>$baby_id,'type'=>1])->max('id');
        if(!$id){
            self::$error = '无身高测评记录';
            return  false;
        }
        return self::check_result($user_id, $id);
    }

    public static function height_log_del($user_id,$id)
    {
        $model = new BabyCheck();
        $res = $model->where(['id'=>$id,'user_id'=>$user_id])->update(['is_del'=>time()]);
        if($res){
            return true;
        }
        return '该记录不存在';
    }

    public static function height_log_list($user_id,$get,$page,$size)
    {
        $baby_model = new Baby();
        $baby_id = $baby_model->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->value('id');
        if(!$baby_id){
            self::$error = '暂无默认宝宝';
            return  false;
        }
        $model = new BabyCheck();

        $where = [
            ['baby_id','=',$baby_id],
            ['type','=',1]
        ];

        $list = $model->where($where)
            ->field("id,check_date,create_time,baby_data,check_data,check_result")
            ->order('check_date desc,id desc')
            ->group('check_date')
            ->page($page,$size)
            ->select()->toArray();

        $count = $model->where($where)
            ->order('check_date desc,id desc')
            ->count();

        $grouped_logs = [];
        foreach ($list as &$log) {
            $baby_data     = json_decode($log['baby_data'],true);
            $check_data     = json_decode($log['check_data'],true);
            $birthday = $baby_data['birthday'];
            $age = now_age($birthday,$log['check_date'],true);
            $log['height'] = $check_data['height'];
            $log['weight'] = $check_data['weight'];
            $log['create_time'] = date('Y-m-d',$log['create_time']);
            $log['check_date'] = date('Y-m-d',$log['check_date']);
            $log['age'] = $age;
            unset($log['baby_data']);
            unset($log['check_data']);
            // // unset($log['create_time']);
            //
            // unset($log['check_date']);
            // if (!isset($grouped_logs[$age])) {
            //     $grouped_logs[$age] = [];
            // }
            // $grouped_logs[$age][] = $log;
        }

        $data = [
            // 'lists' => $grouped_logs,
            'lists' => $list,
            'page'  => $page,
            'size'  => $size,
            'count' => $count,
            'more'  => is_more($count, $page, $size)
        ];
        return $data;
    }

    public static function sport_check_select($user_id,$grade_id)
    {
        $baby_model = new Baby();
        $sex = $baby_model->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->value('sex');
        if(!$sex){
            self::$error = '暂无默认宝宝';
            return  false;
        }
        return sport_grade_to_select($grade_id,$sex,true);
    }

    public static function sport_check($user_id,$data)
    {
        $baby_data = Db::name('baby')->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->find();
        if(!$baby_data){
            self::$error =  '宝贝错误';
            return  false;
        }
        $post_time = strtotime(date('Y-m-d'));

        $grade = $data['grade']??'';
        $grade_arr = sport_grade_arr();
        $grade_id_arr = array_column($grade_arr,'id');
        if(!in_array($grade,$grade_id_arr)){
            self::$error =  '年级错误';
            return  false;
        }
        $check_result = [];
        $ques_arr = sport_grade_to_select($grade,$baby_data['sex']);
        $ques_k_arr = array_column($ques_arr,'name','key');
        $danwei_k_arr = array_column($ques_arr,'dw','key');
        foreach ($ques_k_arr as $k => $v){
            if(empty($data[$k])){
                self::$error =  $v.'错误';
                return  false;
            }

            $dw = $danwei_k_arr[$k];
            if($k == 'chinning'){
                $functionName = "sport_sit_up_result";
            }else if(in_array($k,['run_400','run_800','run_1000'])){
                $functionName = "sport_run_else_result";
            }else{
                $functionName = "sport_".$k."_result";
            }
            if (function_exists($functionName)) {
                $result = $functionName($grade, $data[$k], $baby_data['sex']);
            } else {
                self::$error = '系统错误';
                return false;
            }
            $sug_key = '';
            switch ($k){
                case 'bmi':
                    switch ($result['name']){
                        case '正常':
                            $sug_key = 'bmi_0';
                            break;
                        case '低体重':
                            $sug_key = 'bmi_1';
                            break;
                        case '超重':
                            $sug_key = 'bmi_2';
                            break;
                        case '肥胖':
                            $sug_key = 'bmi_3';
                            break;
                    }
                    break;
                case 'vital_capacity':
                case 'run_50':
                case 'sit_and_reach':
                case 'skip':
                case 'jump':
                case 'sit_up':
                    switch ($result['name']){
                        case '优秀':
                            $sug_key = $k.'1';
                            break;
                        case '良好':
                            $sug_key = $k.'2';
                            break;
                        case '及格':
                            $sug_key = $k.'3';
                            break;
                        case '不及格':
                            $sug_key = $k.'4';
                            break;
                    }
                    break;
                case 'chinning':
                    switch ($result['name']){
                        case '优秀':
                            $sug_key = 'sit_up1';
                            break;
                        case '良好':
                            $sug_key = 'sit_up2';
                            break;
                        case '及格':
                            $sug_key = 'sit_up3';
                            break;
                        case '不及格':
                            $sug_key = 'sit_up4';
                            break;
                    }
                    break;
                case 'run_400'://
                case 'run_800':
                case 'run_1000':
                    switch ($result['name']){
                        case '优秀':
                            $sug_key = 'run_else1';
                            break;
                        case '良好':
                            $sug_key = 'run_else2';
                            break;
                        case '及格':
                            $sug_key = 'run_else3';
                            break;
                        case '不及格':
                            $sug_key = 'run_else4';
                            break;
                    }
                    break;
            }
            if(!$sug_key){
                $result['sug'] =  '未知';
            }else{
                $result['sug'] =  ConfigServer::get('baby_sport',$sug_key);
            }

            $check_result[] = [
                'key'       => $k,
                'name'      => $v,
                'post'      => $data[$k],
                'result'     => $result,
                'dw'        => $dw,
            ];
        }

        $time = time();
        $check_data = json_encode($data);
        $check_result = json_encode($check_result);
        $baby_id = $baby_data['id'];
        $baby_data = json_encode($baby_data);
        $insert_id = Db::name('baby_check')->insertGetId([
            'user_id'       => $user_id,
            'baby_id'       => $baby_id,
            'type'          => 5,
            'check_data'    => $check_data,
            'check_date'    => $post_time,
            'create_time'   => $time,
            'check_result'  => $check_result,
            'baby_data'     => $baby_data,
        ]);
        return [
            'id'    => $insert_id,
            'type'  => 5
        ];
    }

    public static function all_check_result($sn){//$user_id,$data
        $user_id = Db::name('user')->where('sn',$sn)->value('id');
        $baby_model = new Baby();
        $baby_id = $baby_model->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->value('id');
        if(!$baby_id){
            self::$error = '暂无默认宝宝';
            return  false;
        }
        $model = new BabyCheck();
        $return = [];

        $near_height_list = [];
        $height_list = $model->where(['baby_id'=>$baby_id,'type'=>1])->group('check_date')->order('check_date desc')->limit(3)->select()->toArray();
        if(!empty($height_list)){
            foreach ($height_list as $v){
                $check_data = json_decode($v['check_data'],true);
                $baby_data = json_decode($v['baby_data'],true);
                $near_height_list[] = [
                    'id'    => $v['id'],
                    'check_date'  => date('Y-m-d',$v['check_date']),
                    'height'    => $check_data['height'],
                    'height_pot'    => $check_data['height_pot'],
                    'ai_predicting_height'  => round($check_data['ai_predicting_height'],2),
                    'bf_predicting_height'  => round($check_data['bf_predicting_height'],2),
                    'genetic_height'    => $baby_data['genetic_height'],
                    'sup_height'        => $baby_data['sup_height'],
                    'genetic_height_cha' => round($check_data['height'] - $baby_data['genetic_height'],2),
                    'sup_height_cha'   => round($check_data['height'] - $baby_data['sup_height'],2),
                ];
            }
        }
        $return['near_height_list'] = $near_height_list;
        $height_id = $model->where(['baby_id'=>$baby_id,'type'=>1])->max('id');
        if($height_id){
            $return['height_check'] = self::check_result($user_id, $height_id);
        }
        $sleep_id = $model->where(['baby_id'=>$baby_id,'type'=>3])->max('id');
        if($sleep_id){
            $return['sleep_check'] = self::check_result($user_id, $sleep_id);
        }
        $spleen_id = $model->where(['baby_id'=>$baby_id,'type'=>2])->max('id');
        if($spleen_id){
            $return['spleen_check'] = self::check_result($user_id, $spleen_id);
        }
        $sport_id = $model->where(['baby_id'=>$baby_id,'type'=>5])->max('id');
        if($sport_id){
            $return['sport_check'] = self::check_result($user_id, $sport_id);
        }
        $nutrition_id = $model->where(['baby_id'=>$baby_id,'type'=>6])->max('id');
        if($nutrition_id){
            $return['nutrition_check'] = self::check_result($user_id, $nutrition_id);
        }
        $near_nutrition_list = [];
        $nutrition_list = $model->where(['baby_id'=>$baby_id,'type'=>6])->group('check_date')->order('check_date desc')->limit(3)->select()->toArray();
        if(!empty($nutrition_list)){
            foreach ($nutrition_list as $v){
                $near_nutrition_list[] = [
                    'id'    => $v['id'],
                    'check_date'  => date('Y-m-d',$v['check_date']),
                    'check_result'    => json_decode($v['check_result'],true),
                ];
            }
        }
        $return['near_nutrition_list'] = $near_nutrition_list;
        $return['baby_info'] = self::baby_info($user_id);
        return $return;
    }

    public static function nutrition_check($user_id,$data)
    {
        $baby_data = Db::name('baby')->where(['user_id'=>$user_id,'del'=>0,'is_default'=>1])->find();
        if(!$baby_data){
            self::$error =  '宝贝错误';
            return  false;
        }
        $baby_id = $baby_data['id'];
        $post_time = strtotime(date('Y-m-d'));

        if(empty($data)){
            self::$error =  '错误';
            return  false;
        }

        $time = time();
        $check_data = json_encode($data);

        $all_child = array_merge(...array_column($data, 'child'));

        $id_to_value = array_column($all_child, 'value','id');

        $all_id = array_column(array_filter($all_child, function($child) {
            return $child['value'] >= 0;
        }), 'id');

        $all_food = [];

        if(!empty($all_id)){
            $all_food = Db::name('nutrition_food')->where('id','in',$all_id)->column('*','id');
        }


        $check_result = [
            'protein_lysine' => [
                'name'  => '蛋白质/赖氨酸',
                'num'   => 0,
                'points' => 0,
                'value' => '摄入不足',
                'value_img' => 1,
                'key'   => 'protein_lysine',
            ],
            'vb' => [
                'name'  => 'B族维生素',
                'num'   => 0,
                'points' => 0,
                'value' => '摄入不足',
                'value_img' => 1,
                'key'   => 'vb',
            ],
            'vd' => [
                'name'  => '维生素D',
                'num'   => 0,
                'points' => 0,
                'value' => '摄入不足',
                'value_img' => 1,
                'key'   => 'vd',
            ],
            'iron' => [
                'name'  => '铁',
                'num'   => 0,
                'points' => 0,
                'value' => '摄入不足',
                'value_img' => 1,
                'key'   => 'iron',
            ],
            'zinc' => [
                'name'  => '锌',
                'num'   => 0,
                'points' => 0,
                'value' => '摄入不足',
                'value_img' => 1,
                'key'   => 'zinc',
            ],
            'va' => [
                'name'  => '维生素A',
                'num'   => 0,
                'points' => 0,
                'value' => '摄入不足',
                'value_img' => 1,
                'key'   => 'va',
            ],
            'calcium' => [
                'name'  => '钙',
                'num'   => 0,
                'points' => 0,
                'value' => '摄入不足',
                'value_img' => 1,
                'key'   => 'calcium',
            ],
            'dietary_fiber' => [
                'name'  => '膳食纤维',
                'num'   => 0,
                'points' => 0,
                'value' => '摄入不足',
                'value_img' => 1,
                'key'   => 'dietary_fiber',
            ],
            'unhealthy' => [
                'name'  => '不健康食物',
                'num'   => 0,
                'points' => 0,
                'value' => '摄入过量',
                'value_img' => 1,
                'key'   => 'unhealthy',
            ],
        ];
        $sug_arr = [];
        if(!empty($all_food)){
            foreach ($all_food as $fd){
                $multiplier = bcadd($id_to_value[$fd['id']], '1', 2);
                $check_result['protein_lysine']['num'] = bcadd($check_result['protein_lysine']['num'],bcmul($fd['protein_lysine'], $multiplier, 2), 2);
                $check_result['vb']['num'] = bcadd($check_result['vb']['num'],bcmul($fd['vb'], $multiplier, 2),2);
                $check_result['vd']['num'] = bcadd($check_result['vd']['num'],bcmul($fd['vd'], $multiplier, 2),2);
                $check_result['iron']['num'] = bcadd($check_result['iron']['num'],bcmul($fd['iron'], $multiplier, 2),2);
                $check_result['zinc']['num'] = bcadd($check_result['zinc']['num'],bcmul($fd['zinc'], $multiplier, 2),2);
                $check_result['va']['num'] = bcadd($check_result['va']['num'],bcmul($fd['va'], $multiplier, 2),2);
                $check_result['calcium']['num'] = bcadd($check_result['calcium']['num'],bcmul($fd['calcium'], $multiplier, 2),2);
                $check_result['dietary_fiber']['num'] = bcadd($check_result['dietary_fiber']['num'],bcmul($fd['dietary_fiber'], $multiplier, 2),2);
                $check_result['unhealthy']['num'] = bcadd($check_result['unhealthy']['num'],bcmul($fd['unhealthy'], $multiplier, 2),2);

                // $check_result['protein_lysine']['num'] += round($fd['protein_lysine'] * ($id_to_value[$fd['id']] + 1),2);
                // $check_result['vb']['num'] += round($fd['vb'] * ($id_to_value[$fd['id']] + 1),2);
                // $check_result['vd']['num'] += round($fd['vd'] * ($id_to_value[$fd['id']] + 1),2);
                // $check_result['iron']['num'] += round($fd['iron'] * ($id_to_value[$fd['id']] + 1),2);
                // $check_result['zinc']['num'] += round($fd['zinc'] * ($id_to_value[$fd['id']] + 1),2);
                // $check_result['va']['num'] += round($fd['va'] * ($id_to_value[$fd['id']] + 1),2);
                // $check_result['calcium']['num'] += round($fd['calcium'] * ($id_to_value[$fd['id']] + 1),2);
                // $check_result['dietary_fiber']['num'] += round($fd['dietary_fiber'] * ($id_to_value[$fd['id']] + 1),2);
                // $check_result['unhealthy']['num'] += round($fd['unhealthy'] * ($id_to_value[$fd['id']] + 1),2);
            }
        }
// var_dump($check_result);die;
        $now_age_arr = now_age($baby_data['birthday'],$post_time,false);
        $now_year = $now_age_arr['year'];
        if($now_year <= 3){
            $key = 3;
        }else if($now_year > 3 && $now_year <= 6){
            $key = 6;
        }else if($now_year > 6 && $now_year <= 12){
            $key = 12;
        }else{
            $key = 18;
        }
        foreach ($check_result as &$cr){
            $zc = ConfigServer::get('nutrition', $cr['key'].'_'.$key.'_1');
            if($cr['key'] == 'unhealthy'){
                if($cr['num'] > $zc){
                    $sug_arr[] = [
                        'name'  => $cr['name'].'摄入过量',
                        'value' => ConfigServer::get('baby_nutrition', $cr['key'])
                    ];
                    $cr['value_img'] = 1;
                    $cr['value'] = '摄入过量';
                }else{
                    $cr['value_img'] = 3;
                    $cr['value'] = '摄入正常';
                }
            }else{
                $lh = ConfigServer::get('nutrition', $cr['key'].'_'.$key.'_2');
                if($cr['num'] < $zc){
                    $sug_arr[] = [
                        'name'  => $cr['name'].'摄入不足',
                        'value' => ConfigServer::get('baby_nutrition', $cr['key'])
                    ];
                    $cr['value_img'] = 1;
                    $cr['value'] = '摄入不足';
                }else if($cr['num'] >= $lh){
                    $cr['value_img'] = 2;
                    $cr['value'] = '摄入良好';
                }else{
                    $cr['value_img'] = 3;
                    $cr['value'] = '摄入正常';
                }
            }

            $num1 = ConfigServer::get('nutrition', $cr['key'].'_points_'.$key.'_1');
            $num2 = ConfigServer::get('nutrition', $cr['key'].'_points_'.$key.'_2');
            $num3 = ConfigServer::get('nutrition', $cr['key'].'_points_'.$key.'_3');
            $num4 = ConfigServer::get('nutrition', $cr['key'].'_points_'.$key.'_4');
            $num5 = ConfigServer::get('nutrition', $cr['key'].'_points_'.$key.'_5');
            $num6 = ConfigServer::get('nutrition', $cr['key'].'_points_'.$key.'_6');
            $num7 = ConfigServer::get('nutrition', $cr['key'].'_points_'.$key.'_7');
            if($cr['key'] == 'unhealthy'){
                if($cr['num'] > $num1){
                    $cr['points'] = 0;
                }else if($cr['num'] <= $num1 && $cr['num'] > $num2){
                    $cr['points'] = 1;
                }else if($cr['num'] <= $num2 && $cr['num'] > $num3){
                    $cr['points'] = 2;
                }else if($cr['num'] <= $num3 && $cr['num'] > $num4){
                    $cr['points'] = 3;
                }else if($cr['num'] <= $num4 && $cr['num'] > $num5){
                    $cr['points'] = 4;
                }else if($cr['num'] <= $num5 && $cr['num'] > $num6){
                    $cr['points'] = 5;
                }else if($cr['num'] <= $num6 && $cr['num'] > $num7){
                    $cr['points'] = 6;
                }else{
                    $cr['points'] = 7;
                }
            }else{
                if($cr['num'] < $num1){
                    $cr['points'] = 0;
                }else if($cr['num'] >= $num1 && $cr['num'] < $num2){
                    $cr['points'] = 1;
                }else if($cr['num'] >= $num2 && $cr['num'] < $num3){
                    $cr['points'] = 2;
                }else if($cr['num'] >= $num3 && $cr['num'] < $num4){
                    $cr['points'] = 3;
                }else if($cr['num'] >= $num4 && $cr['num'] < $num5){
                    $cr['points'] = 4;
                }else if($cr['num'] >= $num5 && $cr['num'] < $num6){
                    $cr['points'] = 5;
                }else if($cr['num'] >= $num6 && $cr['num'] < $num7){
                    $cr['points'] = 6;
                }else{
                    $cr['points'] = 7;
                }
            }

        }

        $check_result = array_values($check_result);
        $result = compact('sug_arr','check_result');
        $check_result = json_encode($result);
        // $result_key = spleen_key($total_num);
        // $result_name = 'result_'.$result_key;
        // $check_result = ConfigServer::get('baby_spleen', $result_name);
        $baby_data = json_encode($baby_data);
        $insert_id = Db::name('baby_check')->insertGetId([
            'user_id'       => $user_id,
            'baby_id'       => $baby_id,
            'type'          => 6,
            'check_data'    => $check_data,
            'check_date'    => $post_time,
            'create_time'   => $time,
            'check_result'  => $check_result,
            'baby_data'     => $baby_data,
        ]);
        return [
            'id'    => $insert_id,
            'type'  => 6
        ];
    }
}