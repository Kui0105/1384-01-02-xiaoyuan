<?php


namespace app\admin\model;

use think\Model;

class NutritionCategory extends Model
{

    public function getCreateTimeAttr($value, $data)
    {
        if($value){
            return date('Y-m-d H:i:s', $value);
        }
        return $value;
    }

    public function getImgAttr($value, $data)
    {
        if($value){
            return addImgDomain($value);
        }
        return $value;
    }

}