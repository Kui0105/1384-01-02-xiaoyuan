<?php


namespace app\admin\model;

use think\Model;

class NutritionFood extends Model
{

    public function getCreateTimeAttr($value, $data)
    {
        if($value){
            return date('Y-m-d H:i:s', $value);
        }
        return $value;
    }

    public function getProteinLysineAttr($value){
        if($value > 0){
            return floatval($value).' g';
        }
        return '/';
    }

    public function getVbAttr($value){
        if($value > 0){
            return floatval($value).' mg';
        }
        return '/';
    }

    public function getVdAttr($value){
        if($value > 0){
            return floatval($value).' ug';
        }
        return '/';
    }

    public function getIronAttr($value){
        if($value > 0){
            return floatval($value).' mg';
        }
        return '/';
    }

    public function getZincAttr($value){
        if($value > 0){
            return floatval($value).' mg';
        }
        return '/';
    }

    public function getVaAttr($value){
        if($value > 0){
            return floatval($value).' ug';
        }
        return '/';
    }

    public function getCalciumAttr($value){
        if($value > 0){
            return floatval($value).' mg';
        }
        return '/';
    }

    public function getDietaryFiberAttr($value){
        if($value > 0){
            return floatval($value).' g';
        }
        return '/';
    }

}