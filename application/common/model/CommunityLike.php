<?php

namespace app\common\model;
use think\Model;

class CommunityLike extends Model{

    public function getCreateTimeAttr($value, $data)
    {
        if($value){
            return getCTime($value);
        }
        return $value;
    }

}