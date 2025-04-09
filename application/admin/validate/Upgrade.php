<?php


namespace app\admin\validate;

use app\admin\logic\UpgradeLogic;
use think\facade\Cache;
use think\Validate;

class Upgrade extends Validate
{

    protected $rule = [
        'version_no' => 'require|checkIsAbleUpgrade',
        'package_link' => 'require'
    ];

    protected $message = [
        'version_no.require' => '参数缺失',
        'package_link.require' => '参数缺失',
    ];



    //检查是否可以更新
    protected function checkIsAbleUpgrade($value, $rule, $data)
    {
        //验证open_basedir
        $open_basedir = ini_get('open_basedir');
        if (strpos($open_basedir, 'server') !== false) {
            return '请临时关闭服务器本站点的跨域攻击设置，并重启 nginx，具体参考相关升级文档';
        }

        //验证版本信息
        $version_data = local_version();
        //本地版本号
        $local_version = UpgradeLogic::formatVersion($version_data['version']);
        //目标版本号
        $target_version = UpgradeLogic::formatVersion($value);

        //检查一分钟内是否多次操作
        $checkIsRepeat = UpgradeLogic::getUpgradeLock($target_version);
        if (!empty($checkIsRepeat)) {
            return '正在执行更新，请1分钟后重试……';
        } else {
            UpgradeLogic::setUpgradeLock($target_version);
        }

        //本地版本需要小于当前选中版本
        if ($local_version > $target_version) {
            return '当前系统无法升级到该版本，请重新选择更新版本。';
        }

        //获取远程列表
        $remote_data = UpgradeLogic::getRemoteVersion()['lists'] ?? [];

        if (empty($remote_data)) {
            return '获取更新数据失败。';
        }

        foreach ($remote_data as $k => $item) {
            if ($item['version_no'] != $local_version) {
                continue;
            }

            if (empty($remote_data[$k-1])) {
                return '已为最新版本。';
            }

            if ($remote_data[$k-1]['version_no'] != $target_version) {
                return '当前系统无法升级到该版本，请重新选择更新版本。';
            }
        }
        return true;
    }

}