<?php


namespace app\common\command;


use app\admin\logic\WechatMerchantTransferLogic;
use app\admin\model\WithdrawApply;
use app\common\logic\AccountLogLogic;
use app\common\model\AccountLog;
use app\common\model\User;
use app\common\model\Withdraw;
use app\common\server\ConfigServer;
use think\console\Command;
use think\console\Output;
use think\console\Input;
use think\facade\Log;

class WechatMerchantTransfer extends Command
{
    protected function configure()
    {
        $this->setName('wechat_merchant_transfer')
            ->setDescription('商家转账到零钱查询');
    }

    protected function execute(Input $input, Output $output)
    {
        //微信零钱接口:1-企业付款到零钱;2-商家转账到零钱
        $transfer_way = ConfigServer::get('withdraw', 'transfer_way',1);
        //选择了商家转账到零钱再进行查询
        if ($transfer_way == 1) {
            return false;
        }

        $lists = WithdrawApply::where(['type'=>Withdraw::TYPE_WECHAT_CHANGE,'status'=>Withdraw::STATUS_ING])
            ->order('id desc')
            ->select();

        foreach ($lists as $list) {
            $result = WechatMerchantTransferLogic::details($list);
            // 记录查询结果
            WithdrawApply::update(['update_time'=>time(),'pay_search_desc'=>json_encode($result, JSON_UNESCAPED_UNICODE)],['id'=>$list['id']]);
            if(isset($result['state'])) {
                if ($result['state'] == 'SUCCESS') {
                    // 转账成功,标记提现申请单为提现成功,记录支付信息
                    WithdrawApply::update(['status'=>3,'payment_no'=>$result['transfer_bill_no'],'payment_time'=>strtotime($result['update_time'])],['id'=>$list['id']]);
                }
                if ($result['state'] == 'FAIL') {
                    // 转账失败
                    WithdrawApply::update(['status'=>4],['id'=>$list['id']]);
                    //回退佣金
                    $user = User::find($list['user_id']);
                    $user->earnings = ['inc', $list['money']];
                    $user->save();

                    //增加佣金变动记录
                    AccountLogLogic::AccountRecord(
                        $list['user_id'],
                        $list['money'],
                        1,
                        AccountLog::withdraw_back_earnings,
                        '',
                        $list['id'],
                        $list['sn']
                    );
                }
                if ($result['state'] == 'PROCESSING') {
                    return ['code' => 0, 'msg' => '正在处理中'];
                }
            }else{
                Log::write($result['message'] ?? '商家转账到零钱查询失败');
                return null;
            }
        }

        return true;
    }
}