<?php



namespace app\common\model;


use think\Model;

/**
 * 砍价活动 助力模型
 * Class BargainKnife
 * @Author 张无忌
 * @package app\common\model
 */
class BargainKnife extends Model
{
    // 关联用户模型
    public function user()
    {
        return $this->hasOne('user', 'id', 'user_id')
            ->field('id,sn,nickname,avatar,level,mobile,sex,create_time');
    }
}