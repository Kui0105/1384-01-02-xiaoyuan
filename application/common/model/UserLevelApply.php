<?php


namespace app\common\model;


use think\Db;
use think\Model;

class UserLevelApply extends Model
{
    /**
     * 会员申请状态
     */
    const STATUS_WAIT_AUDIT    = 1; //待审核
    const STATUS_AUDIT_SUCCESS = 2; //审核通过
    const STATUS_AUDIT_ERROR   = 3; //审核拒绝

    public function user()
    {
        return $this->hasOne('app\common\model\User', 'id', 'user_id');
    }

    public static function getApplyStatus($status = true)
    {
        $desc = [
            self::STATUS_WAIT_AUDIT    => '待审核',
            self::STATUS_AUDIT_SUCCESS => '审核通过',
            self::STATUS_AUDIT_ERROR   => '审核拒绝',
        ];
        if ($status === true) {
            return $desc;
        }
        return $desc[$status] ?? '未知';
    }

    public static function getLevelName($level)
    {
        $desc = [
            1   => '省代',
            2   => '市代',
            3   => '区代',
            4   => '诊所'
        ];
        if ($level === true) {
            return $desc;
        }
        return $desc[$level] ?? '未知';
    }

    public static function getLevelArea($level,$self_id,$pid,$single = false)
    {
        $area_name = '';
        switch ($level){
            case 1:
                $area_name = DevRegion::getArea($self_id);
                break;
            case 2:
                if($single){
                    $province_id = DevRegion::where('id',$self_id)->value('parent_id');
                    $area_name = DevRegion::getArea($province_id).'-'.DevRegion::getArea($self_id);
                }else{
                    $id_arr = explode(',',$self_id);
                    foreach ($id_arr as $v){
                        $province_id = DevRegion::where('id',$v)->value('parent_id');
                        $area_name.= DevRegion::getArea($province_id).'-'.DevRegion::getArea($v).';';
                    }
                }
                break;
            case 3:
                if($single){
                    $city_id = DevRegion::where('id',$self_id)->value('parent_id');
                    $province_id = DevRegion::where('id',$city_id)->value('parent_id');
                    $area_name = DevRegion::getArea($province_id).'-'.DevRegion::getArea($city_id).'-'.DevRegion::getArea($self_id);
                }else{
                    $id_arr = explode(',',$self_id);
                    foreach ($id_arr as $v){
                        $city_id = DevRegion::where('id',$v)->value('parent_id');
                        $province_id = DevRegion::where('id',$city_id)->value('parent_id');
                        $area_name.= DevRegion::getArea($province_id).'-'.DevRegion::getArea($city_id).'-'.DevRegion::getArea($v).';';
                    }
                }
                break;
            case 4:
                $city_id = DevRegion::where('id',$pid)->value('parent_id');
                $province_id = DevRegion::where('id',$city_id)->value('parent_id');
                $area_name = DevRegion::getArea($province_id).'-'.DevRegion::getArea($city_id).'-'.DevRegion::getArea($pid);
                break;
        }
        return $area_name;
    }
}