<?php


namespace app\common\command;

use app\common\server\ConfigServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\facade\Config;
use think\facade\Log;

class Update extends Command
{
    protected function configure()
    {
        $this->setName('update')
            ->setDescription('更新代码、同步数据以后执行');
    }

    protected function execute(Input $input, Output $output)
    {
        //修改旧活动专区错误
        $lists = Db::name('activity_goods')->where(['del' => 0])->select();

        $temp = [];
        $count = 0;

        foreach ($lists as $k => $v) {
            $temp_key = $v['goods_id'].'_'.$v['activity_id'];
            if (in_array($temp_key, $temp)) {
                Db::name('activity_goods')
                    ->where(['del' => 0, 'id' => $v['id']])
                    ->update(['del' => 1]);
                $count += 1;
            } else {
                $temp[] = $temp_key;
            }
        }
        Log::write('处理活动专区错误,删除'.$count.'数据');

        // 更新file_cate表中type=null值的旧记录
        Db::name('file_cate')->whereNull('type')->update(['type' => 1]);
    }

}