<?php


namespace app\common\model;

use app\common\server\UrlServer;
use think\Model;

class User extends Model
{
    /**
     * @notes 统一处理用户nickname
     * @param $nickname
     * @return mixed|string
     * @author lbzy
     * @datetime 2023-10-08 18:40:35
     */
    function getNicknameAttr($nickname)
    {
        if (request()->module() == 'admin' && request()->isAjax()) {
            $nickname = htmlspecialchars($nickname);
        }
        
        return $nickname;
    }
    
    //头像
    public function getAvatarAttr($value, $data)
    {
        if ($value) {
            return UrlServer::getFileUrl($value);
        }
        return $value;
    }

    public function getUserMoneyAttr($value)
    {
        return $value ? $value : 0;
    }

    public function getEarningsAttr($value)
    {
        return $value ? $value : 0;
    }

    public function getBaseAvatarAttr($value, $data)
    {
        return $data['avatar'];
    }

    public function getBirthdayAttr($value)
    {
        return empty($value) ? '' : date('Y-m-d H:i:s', $value);
    }

    public function getLoginTimeAttr($value)
    {
        return empty($value) ? '' : date('Y-m-d H:i:s', $value);
    }

    //加入时间
    public function getCreateTimeAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $value);
    }

    //性别转换
    public function getSexAttr($value, $data)
    {
        switch ($value) {
            case 1:
                return '男';
            case 2:
                return '女';
            default:
                return '未知';
        }
    }

    public function level()
    {
        return $this->hasOne('UserLevel','id', 'level');
    }

    public static function getUserInfo($userId)
    {
        $user = self::field('id,sn,nickname,avatar')->findOrEmpty($userId)->toArray();
        if (empty($user)) {
            return '系统';
        }
        $user['avatar'] = empty($user['avatar']) ? '' : UrlServer::getFileUrl($user['avatar']);
        return $user;
    }

    public function getDistributionAttr($value)
    {
        $distribution = Distribution::where('user_id', $value)->findOrEmpty()->toArray();
        if (!empty($distribution) && $distribution['is_distribution'] == 1) {
            return '是';
        }
        return '否';
    }

    public function searchDistributionAttr($query, $value, $params)
    {
        // 非分销会员
        if (isset($params['is_distribution']) && $params['is_distribution'] != 'all' && $params['is_distribution'] == 0) {
            $ids = Distribution::where('is_distribution', 1)->column('user_id');
            if (!empty($ids)) {
                $query->where('id', 'not in', $ids);
            }
        }
        // 分销会员
        if (isset($params['is_distribution']) && $params['is_distribution'] != 'all' && $params['is_distribution'] == 1) {
            $ids = Distribution::where('is_distribution', 1)->column('user_id');
            if (!empty($ids)) {
                $query->where('id', 'in', $ids);
            }
        }
    }
}
