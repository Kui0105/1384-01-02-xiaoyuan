<?php

namespace app\api\model;
use think\Model;

class GoodsSpec extends Model {
    public function GoodsSpecValue(){
        return $this->hasMany('GoodsSpecValue','spec_id','id');
    }
}