<?php

namespace app\common\logic;
use app\common\model\User;
use app\common\model\AccountLog;
use app\common\server\ConfigServer;
use think\Db;

class AccountLogLogic{

    //积分赠送
    public static function sendPoint($user_id,$type)
    {
        $send_integral = ConfigServer::get('point_way',$type,0);
        if($send_integral){
            switch ($type){
                case 'height':
                    $source_type = AccountLog::check_height;
                    break;
                case 'weight':
                    $source_type = AccountLog::check_weight;
                    break;
                case 'like':
                    $source_type = AccountLog::community_like;
                    break;
                case 'comment':
                    $source_type = AccountLog::community_comment;
                    break;
                case 'order':
                    $source_type = AccountLog::order_send;
                    break;
                case 'invite':
                    $source_type = AccountLog::invite_user;
                    break;
                case 'share':
                    $source_type = AccountLog::share_article;
                    break;
                case 'video':
                    $source_type = AccountLog::watch_video;
                    break;
            }
            if($type == 'height'){
                $month_num = AccountLog::where(['source_type'=>$source_type,'user_id'=>$user_id])->whereTime('create_time', 'month')->count();
                if($month_num){
                    return true;
                }
            }else{
                $send_limit = ConfigServer::get('point_way',$type.'_limit',0);
                if($send_limit){
                    $now_num = AccountLog::where(['source_type'=>$source_type,'user_id'=>$user_id])->whereTime('create_time', 'today')->count();
                    if($send_limit <= $now_num){
                        return true;
                    }
                }
            }

            Db::name('user')->where(['id'=>$user_id])->lock(true)->value('user_integral');
            Db::name('user')->where(['id'=>$user_id])->setInc('user_integral',$send_integral);
            self::AccountRecord($user_id,$send_integral,1, $source_type);
        }
    }
    /**
     * Notes:记录会员账户流水，如果变动类型是成长值，且是增加的，则调用更新会员等级方法。该方法应在添加用户账户后调用
     * @author: cjh 2020/12/15 11:49
     * @param int $user_id  用户id
     * @param float $amount 变动数量
     * @param int $change_type  变动类型：1-增加；2-减少
     * @param int $source_type 来源类型
     * @param string $remark 说明
     * @param string $source_id 来源id
     * @param string $source_sn 来源单号
     * @param string $extra 额外字段说明
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function AccountRecord($user_id,$amount,$change_type,$source_type,$remark ='',$source_id ='',$source_sn='',$extra=''){
        $user = User::get($user_id);
        if(empty($user)){
            return false;
        }
        $type = AccountLog::getChangeType($source_type);
        $left_amount = '';
        switch ($type){
            case 'money':
                $left_amount = $user->user_money;
                break;
            case 'integral':
                $left_amount = $user->user_integral;
                break;
            case 'growth':
                $left_amount = $user->user_growth;
                break;
            case 'earnings':
                $left_amount = $user->earnings;
        }
        $account_log = new AccountLog();
        $account_log->log_sn = createSn('account_log','log_sn','',4);
        $account_log->user_id = $user_id;
        $account_log->source_type = $source_type;
        $account_log->source_id = $source_id;
        $account_log->source_sn = $source_sn;
        $account_log->change_amount = $amount;
        $account_log->left_amount = $left_amount;
        $account_log->remark = AccountLog::getRemarkDesc($source_type,$source_sn,$remark);
        $account_log->extra = $extra;
        $account_log->change_type = $change_type;
        $account_log->create_time = time();
        $account_log->save();

        //更新会员等级
        if($type === 'growth' && $change_type == 1){
            UserLevelLogic::updateUserLevel($user_id);
        }
        return true;

    }
}