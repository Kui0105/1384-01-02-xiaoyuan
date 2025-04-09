<?php

namespace app\admin\logic;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;

class MapConfigLogic{
    /**
     * @notes 获取地图配置
     * @return array
     * @author ljj
     * @date 2021/8/20 8:34 下午
     */
    public static function getConfig(){
        $config = [
            'tx_map_key'   => ConfigServer::get('map','tx_map_key',''),
        ];
        return $config;

    }

    /**
     * @notes 地图设置
     * @param $post
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author ljj
     * @date 2021/8/20 8:34 下午
     */
    public static function setConfig($post)
    {
        ConfigServer::set('map','tx_map_key',$post['tx_map_key']);
    }
}