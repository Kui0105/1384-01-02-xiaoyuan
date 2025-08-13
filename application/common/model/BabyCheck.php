<?php

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class BabyCheck extends Model
{

    use SoftDelete;
    protected $deleteTime = 'is_del';
    protected $defaultSoftDelete = 0;


    public static function get_check_baby($baby)
    {
        $user_data  = json_decode($baby['baby_data'],true);
        $birthday   = $user_data['birthday'];
        $check_age  = now_age($birthday,$baby['check_date'],true);
        $return_data = [
            'name'  => $user_data['name'],
            'sex'   => $user_data['sex'],
            'age'   => $check_age,
            'check_date'    => date('Y-m-d',$baby['check_date']),
            'head_img'      => addImgDomain($user_data['head_img']),
            'genetic_height'=> $user_data['genetic_height'],
            'sup_height'    => $user_data['sup_height'],
            'birthday'  => $user_data['birthday'],
            'height'  => $user_data['height'],
            'weight'  => $user_data['weight']
        ];
        return $return_data;
    }
}