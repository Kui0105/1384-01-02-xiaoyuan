<?php

namespace app\common\model;
use think\Model;

class Community extends Model{

    public function getCreateTimeAttr($value, $data)
    {
        if($value){
            return getCTime($value);
        }
        return $value;
    }

    public function getImgsAttr($value, $data)
    {
        if($value){
            return  addImgDomain(explode(',',$value));
        }
        return $value;
    }
}