<?php



namespace app\common\model;


use think\Model;

/**
 * 拼团开团
 * Class TeamFound
 * @package app\common\model
 */
class TeamFound extends Model
{
    // 关联用户模型
    public function user()
    {
        return $this->hasOne('user', 'id', 'user_id')
            ->field('id,sn,nickname,avatar,level,mobile,sex,create_time');
    }

    public function teamFollow()
    {
        return $this->hasMany('team_follow', 'found_id', 'id');
    }
}