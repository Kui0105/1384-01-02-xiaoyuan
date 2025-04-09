<?php

namespace app\api\model;
use app\common\server\UrlServer;
use think\Model;

class GoodsImage extends Model{
    public function getUriAttr($value,$data){
        return UrlServer::getFileUrl($value);
    }
}