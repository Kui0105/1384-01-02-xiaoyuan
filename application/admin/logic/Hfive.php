<?php


namespace app\admin\logic;

use app\common\server\ConfigServer;
use think\facade\Env;

/**
 * H5商城逻辑层
 * Class Hfive
 * @package app\admin\logic
 */
class Hfive
{
    /**
     * @notes 获取H5商城设置
     * @return array
     * @author Tab
     * @date 2021/8/14 14:56
     */
    public static function getConfig()
    {
        $config = [
            'is_open' => ConfigServer::get('h5', 'is_open', 1),
            'page' => ConfigServer::get('h5', 'page', 1),
            'page_url' => ConfigServer::get('h5', 'page_url', ''),
            'h5_url' => request()->domain() . '/mobile'
        ];

        return $config;
    }

    /**
     * @notes H5商城设置
     * @param $params
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author Tab
     * @date 2021/8/14 14:57
     */
    public static function set($params)
    {
        ConfigServer::set('h5', 'is_open', $params['is_open']);
        ConfigServer::set('h5', 'page', $params['page']);
        ConfigServer::set('h5', 'page_url', $params['page_url']);

        // 恢复原入口
        if(file_exists('./mobile/index_lock.html')) {
            // 存在则原商城入口被修改过，先清除修改后的入口
            unlink('./mobile/index.html');
            // 恢复原入口
            rename('./mobile/index_lock.html', './mobile/index.html');
        }

        // H5商城关闭 且 显示空白页
        if($params['is_open'] == 0 && $params['page'] == 1) {
            // 变更文件名
            rename('./mobile/index.html', './mobile/index_lock.html');
            // 创建新空白文件
            $newfile = fopen('./mobile/index.html', 'w');
            fclose($newfile);
        }

        // H5商城关闭 且 跳转指定页
        if($params['is_open'] == 0 && $params['page'] == 2 && !empty($params['page_url'])) {
            // 变更文件名
            rename('./mobile/index.html', './mobile/index_lock.html');
            // 创建重定向文件
            $newfile = fopen('./mobile/index.html', 'w');
            $content = '<script>window.location.href = "' . $params['page_url'] . '";</script>';
            fwrite($newfile, $content);
            fclose($newfile);
        }
    }
}