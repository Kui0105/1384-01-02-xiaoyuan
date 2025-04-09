<?php

namespace app\admin\logic;

use app\common\logic\LogicBase;
use app\common\model\UserLevel;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\Db;

/**
 * 用户设置逻辑层
 * Class UserLogic
 * @package app\admin\logic\setting
 */
class UserSettingLogic extends LogicBase
{
    /**
     * @notes 获取用户配置
     * @return array
     * @author Tab
     */
    public static function getConfig()
    {
        $config = [
            // 邀请下级 0-关闭 1-开启(默认)
            'is_open' => ConfigServer::get('invite', 'is_open', 1),
            // 邀请下级资格 1-全部用户(默认) 2-分销会员
            'qualifications' => ConfigServer::get('invite', 'qualifications', [1]),
            // 成为下级条件 1-邀请码(默认)
            'condition' => ConfigServer::get('invite', 'condition', 1),
            // 自定义邀请海报
            'poster' => ConfigServer::get('invite', 'poster', '/images/share/share_user_bg.png'),
            //指定会员
            'invite_appoint_user' => ConfigServer::get('invite', 'invite_appoint_user', []),
        ];
        $config['poster'] = empty($config['poster']) ? UrlServer::getFileUrl('images/share/share_user_bg.png') : UrlServer::getFileUrl($config['poster']);
        return $config;
    }

    /**
     * @notes 用户设置
     * @param $params
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     */
    public static function set($params)
    {
        try {
            if(!isset($params['poster']) || empty($params['poster'])) {
                throw new \Exception('请选择自定义海报');
            }
            if(!isset($params['qualifications'])) {
                throw new \Exception('请至少选择一种分销资格');
            }
            //兼容以前版本,保存数据格式
            $params['qualifications'] = [$params['qualifications']];
            if(count($params['qualifications']) >= 2){
                throw new \Exception('分销资格只能选择一种');
            }
            $allowFields = ['is_open', 'qualifications', 'condition', 'poster','invite_appoint_user'];
            if(in_array(2,$params['qualifications'])){
                if(!isset($params['invite_appoint_user']) || empty($params['invite_appoint_user'])){
                    throw new \Exception('请选择指定会员等级');
                }
                $user_level = self::getUserLevel();
                $user_level = array_column($user_level,'id');
                $ids = [];
                foreach ($params['invite_appoint_user'] as $id =>$val) {
                    if(!in_array($id,$user_level)){
                        throw new \Exception('用户等级错误，请刷新页面');
                    }
                    $ids[] = $id;
                }
                $params['invite_appoint_user'] = $ids;
            }else{

                $params['invite_appoint_user'] = [];

            }

            foreach ($allowFields as $field) {
                if(isset($params[$field])) {
                    $params[$field] = is_array($params[$field]) ? json_encode($params[$field], JSON_UNESCAPED_UNICODE) : $params[$field];
                    $params[$field] = $field == 'poster' ? UrlServer::setFileUrl($params[$field]) : $params[$field];
                    ConfigServer::set('invite', $field, $params[$field]);
                }
            }
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 获取会员等级
     * @author cjhao
     * @date 2022/2/25 16:10
     */
    public static function getUserLevel()
    {
        return UserLevel::getSelectList();
    }
}