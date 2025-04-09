<?php



namespace app\admin\logic;


use app\common\model\Crontab_;
use Cron\CronExpression;
use think\Db;
use think\Exception;

class CrontabLogic
{

    /**
     * 获取定时任务列表
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function lists()
    {

        $lists = Db::name('dev_crontab')
            ->order(['system' => 'desc'])
            ->select();
        foreach ($lists as $k => $v) {
            $lists[$k]['type_str'] = Crontab_::$type[$v['type']];
            $lists[$k]['last_time_str'] = empty($v['last_time']) ? '-' : date('m-d H:i:s', $v['last_time']);
            if ($v['type'] == 2) {
                $lists[$k]['expression'] = '-';
            }
        }
        return ['count' => count($lists), 'lists' => $lists];
    }

    /**
     * 定时任务信息
     * @param $id
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function info($id)
    {
        return Db::name('dev_crontab')
            ->where(['id' => $id])
            ->find();
    }

    /**
     * 获取接下来几次执行时间
     * @param $get
     * @return array
     */
    public static function expression($get)
    {
        if (CronExpression::isValidExpression($get['expression']) === false) {
            return [['time' => 0, 'date' => '规则设置错误']];
        }
        $cron_expression = CronExpression::factory($get['expression']);
        try {
            $res = $cron_expression->getMultipleRunDates(5);
        } catch (Exception $e) {
            return [['time' => 0, 'date' => '规则设置错误']];
        }
        $res = json_decode(json_encode($res), true);
        $lists = [];
        foreach ($res as $k => $v) {
            $lists[$k]['time'] = $k + 1;
            $lists[$k]['date'] = str_replace('.000000', '', $v['date']);
        }
        $lists[] = ['time' => 'x', 'date' => '……'];
        return $lists;
    }


    /**
     * 添加系统任务
     * @param $post
     * @return int|string
     */
    public static function add($post)
    {
        $data = [
            'name' => $post['name'],
            'type' => $post['type'],
            'remark' => $post['remark'],
            'command' => $post['command'],
            'parameter' => $post['parameter'],
            'status' => $post['status'],
            'expression' => $post['expression'],
            'create_time' => time()
        ];
        $result = Db::name('dev_crontab')
            ->insert($data);
        return $result;
    }

    /**
     * 编辑系统任务
     * @param $post
     * @return int|string
     */
    public static function edit($post)
    {
        $data = [
            'name' => $post['name'],
            'type' => $post['type'],
            'remark' => $post['remark'],
            'command' => $post['command'],
            'parameter' => $post['parameter'],
            'status' => $post['status'],
            'expression' => $post['expression'],
            'update_time' => time()
        ];
        $result = Db::name('dev_crontab')
            ->where(['id' => $post['id']])
            ->update($data);
        return $result;
    }

    /**
     * 删除定时任务
     * @param $id
     * @return int
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function del($id)
    {
        $system = Db::name('dev_crontab')
            ->where(['id' => $id])
            ->value('system');
        if ($system === 1) {
            return false;
        }
        $flag = Db::name('dev_crontab')
            ->where(['id' => $id])
            ->delete();
        return $flag;
    }
}