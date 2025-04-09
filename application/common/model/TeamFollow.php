<?php



namespace app\common\model;


use think\Model;

/**
 * 拼团参团
 * Class TeamFollow
 * @package app\common\model
 */
class TeamFollow extends Model
{
    // 关联用户模型
    public function user()
    {
        return $this->hasOne('user', 'id', 'follow_user_id')
            ->field('id,sn,nickname,avatar,level,mobile,sex,create_time');
    }
}