<?php


namespace app\common\server;

use app\common\logic\AccountLogLogic;
use app\common\model\AccountLog;
use app\common\model\User;

class BalancePayServer
{
    protected $error = '未知错误';

    public function pay($from, $order, $order_source)
    {
        try {
            $user = User::findOrEmpty($order['user_id']);
            if($user->isEmpty() || $order['order_amount'] > $user->user_money) {
                throw new \think\Exception('余额不足');
            }

            // 扣减余额
            $user->user_money = $user->user_money - $order['order_amount'];
            $user->save();

            // 记录账户流水
            AccountLogLogic::AccountRecord($order['user_id'], $order['order_amount'],2, AccountLog::balance_pay_order, '', $order['id'], $order['order_sn']);
        } catch(\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getError()
    {
        return $this->error;
    }
}