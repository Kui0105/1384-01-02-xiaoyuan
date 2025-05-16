<?php


namespace app\common\command;

use app\common\model\NoticeSetting;
use app\common\server\ConfigServer;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\facade\Config;
use think\facade\Hook;
use think\facade\Log;

class Medication extends Command
{
    protected function configure()
    {
        $this->setName('medication')
            ->setDescription('用药消息');
    }

    protected function execute(Input $input, Output $output)
    {
        $now = time();
        //修改旧活动专区错误
        $lists = Db::name('hos_product')->where(['del' => 0,'status'=>1])->where(Db::raw("end_time < $now"))->select();
        $count = 0;
        foreach ($lists as $k => $v) {
            $count += 1;
            //发送通知
            Hook::listen('notice', [
                'user_id' => $v['son_id'],
                'name' => $v['name'],
                'scene' => NoticeSetting::HOSPITAL_USER,
            ]);
            $nicknames = Db::name('user')->where('id',$v['son_id'])->value('nickname');
            //发送通知
            Hook::listen('notice', [
                'user_id' => $v['user_id'],
                'nicknames' => $nicknames,
                'name' => $v['name'],
                'scene' => NoticeSetting::HOSPITAL_DOCTOR,
            ]);
            // 更新file_cate表中type=null值的旧记录
            Db::name('hos_product')->where('id',$v['id'])->update(['status' => 2]);
        }
        Log::write('用药消息'.$count.'数据');
    }

}