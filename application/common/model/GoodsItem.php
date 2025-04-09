<?php



namespace app\common\model;


use app\common\server\UrlServer;
use think\Model;

class GoodsItem extends Model
{
    /**
     * @notes notes
     * @param $value
     * @param $data
     * @return string
     * @author lbzy
     * @datetime 2024-04-03 09:53:03
     */
    function getMarketPriceAttr($value, $data)
    {
        return $value <= 0 ? '' : $value;
    }
    
    function getCostPriceAttr($value, $data)
    {
        return $value <= 0 ? '' : $value;
    }
    
    function getWeightAttr($value, $data)
    {
        return $value <= 0 ? '' : $value;
    }
    
    function getVolumeAttr($value, $data)
    {
        return $value <= 0 ? '' : $value;
    }
    
    function getAbsImageAttr($value, $data)
    {
        return UrlServer::getFileUrl($data['image']);
    }
}