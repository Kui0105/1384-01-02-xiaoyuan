<?php

namespace app\common\model;
use think\Model;

class CommunityComment extends Model{

    public function getCreateTimeAttr($value, $data)
    {
        if($value){
            return getCTime($value);
        }
        return $value;
    }

}