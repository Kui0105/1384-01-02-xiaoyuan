<?php


namespace app\common\server;


use think\Db;
use think\facade\Cache;
use think\facade\Config;

class ConfigServer
{
    /**
     * @notes 设置配置值
     * @param string $type
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author 令狐冲
     * @date 2022/9/29 16:03
     */
    public static function set(string $type, $name = '', $value = '')
    {
        $CacheKey = 'config' . '-' . $type . '-' . $name;
        Cache::rm($CacheKey);
        $original = $value;
        $update_time = time();
        if (is_array($value)) {
            $value = json_encode($value, true);
        }
        $data = Db::name('config')
            ->where(['type' => $type, 'name' => $name])
            ->find();
        if (empty($data)) {
            Db::name('config')
                ->insert(['type' => $type, 'name' => $name, 'value' => $value]);
        } else {
            Db::name('config')
                ->where(['type' => $type, 'name' => $name])
                ->update(['value' => $value, 'update_time' => $update_time]);
        }
        return $original;
    }


    /**
     * @notes 获取配置
     * @param string $type
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     * @author 令狐冲
     * @date 2022/9/29 11:35
     */
    public static function get(string $type, $name = '', $defaultValue = NULL)
    {
        //有缓存取缓存
        $CacheKey = 'config' . '-' . $type . '-' . $name;
        $result = Cache::get($CacheKey);
        $value = $result['config_server'] ?? false;
        if ($value !== false) {
            return $value;
        }

        //单项配置
        if ($name) {
            $value = Db::name('config')
                ->where(['type' => $type, 'name' => $name])
                ->value('value');

            //数组配置需要自动转换
            $json = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $json;
            }
            //获取调用默认配置
            if ($value === NULL) {
                $value = $defaultValue;
            }
            //获取系统配置文件的配置
            if ($value === NULL) {
                $value = Config::get('default.' . $type . '.' . $name);
            }
            Cache::set($CacheKey, ['config_server' => $value]);
            return $value;
        }

        //多项配置
        $data = Db::name('config')
            ->where(['type' => $type])
            ->column('value', 'name');
        //数组配置需要自动转换
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $json = json_decode($v, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data[$k] = $json;
                }
            }
        }
        if ($data === []) {
            $data = $defaultValue;
        }
        Cache::set($CacheKey, ['config_server' => $data]);
        return $data;
    }
}