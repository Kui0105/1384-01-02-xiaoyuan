<?php

namespace app\api\model;
use app\common\server\UrlServer;
use think\Model;

class OrderGoods extends Model {
    public function getImageAttr($value,$data){
        if($value){
            return UrlServer::getFileUrl($value);
        }
        return $value;
    }

}