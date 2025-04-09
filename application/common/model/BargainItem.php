<?php



namespace app\common\model;


use think\Model;

/**
 * 砍价活动 商品SKU模型
 * Class BargainItem
 * @Author 张无忌
 * @package app\common\model
 */
class BargainItem extends Model
{
    public function goodsItem()
    {
       return $this->hasOne('GoodsItem', 'id', 'item_id');
    }
}