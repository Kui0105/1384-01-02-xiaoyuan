<?php



namespace app\common\model;


use think\Model;

/**
 * 砍价活动模型
 * Class Bargain
 * @Author 张无忌
 * @package app\common\model
 */
class Bargain extends Model
{
    /**
     * Notes: 关联商品模型
     * @author 张无忌(2021/2/23 17:50)
     */
    public function goods()
    {
        return $this->hasOne('Goods', 'id', 'goods_id')
            ->field(['id', 'name', 'image', 'max_price', 'min_price']);
    }

    /**
     * Notes: 关联砍价参与人数
     * @author 张无忌(2021/2/23 18:06)
     */
    public function launchPeopleNumber()
    {
        return $this->hasMany('BargainLaunch', 'bargain_id', 'id');
    }

    /**
     * Notes: 关联砍价成功人数
     * @author 张无忌(2021/2/23 18:11)
     */
    public function successKnifePeopleNumber()
    {
        return $this->hasMany('BargainLaunch', 'bargain_id', 'id')
            ->where(['status'=>1]);
    }
}