<?php


namespace app\admin\controller;

use app\common\server\ConfigServer;

class ShopSetting extends AdminBase
{

    /**
     * 交易设置
     */
    public function trading()
    {
        $config = [
            'order_contact' => ConfigServer::get('order_message', 'order_contact' ),//订单管理联系人
            'order_contact_mobile' => ConfigServer::get('order_message', 'order_contact_mobile'),//订单管理联系人手机
            'order_cancel' => ConfigServer::get('trading', 'order_cancel', 30),//未付款订单多久时间后自动关闭
            'customer_cancel_limit' => ConfigServer::get('trading', 'customer_cancel_limit', 0),//已支付订单多长时间内允许客户主动取消订单
            'order_finish' => ConfigServer::get('trading', 'order_finish', 7),//已发货订单多久时间后自动收货完成订单
            'refund_days'  => ConfigServer::get('after_sale', 'refund_days', 7),//已完成订单多久时间内允许售后退款
            'deduct_type'  => ConfigServer::get('trading', 'deduct_type', 1),//订单库存扣减方式
            'growth_ratio' => ConfigServer::get('trading', 'growth_ratio', 0),//成长值比例
            'contact'      => ConfigServer::get('shop', 'contact' ),//联系人
            'mobile'       => ConfigServer::get('shop', 'mobile'),//联系手机号
            'province_id'  => ConfigServer::get('shop', 'province_id'),//省份id
            'city_id'      => ConfigServer::get('shop', 'city_id'),//市id
            'district_id'  => ConfigServer::get('shop', 'district_id'),//区id
            'address'      => ConfigServer::get('shop', 'address'),//详细地址
            'give_integral_scene' => ConfigServer::get('trading', 'give_integral_scene', 1),  //赠送积分时机
            'give_growth_scene'   => ConfigServer::get('trading', 'give_growth_scene', 1)     //赠送成长值时机
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }

    public function setTrading()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('trading', 'order_cancel', $post['order_cancel']);
            ConfigServer::set('trading', 'customer_cancel_limit', $post['customer_cancel_limit']);
            ConfigServer::set('trading', 'order_finish', $post['order_finish']);
            ConfigServer::set('after_sale', 'refund_days', $post['refund_days']);
            ConfigServer::set('trading', 'deduct_type', $post['deduct_type']);//订单库存扣减方式
            ConfigServer::set('trading', 'growth_ratio', $post['growth_ratio']);//成长值比例
            ConfigServer::set('shop', 'contact', $post['contact']);//店铺联系人
            ConfigServer::set('shop', 'mobile', $post['mobile']);//店铺联系手机号
            ConfigServer::set('shop', 'province_id', $post['province_id']);//店铺省份id
            ConfigServer::set('shop', 'city_id', $post['city_id']);//店铺市id
            ConfigServer::set('shop', 'district_id', $post['district_id']);//店铺地区id
            ConfigServer::set('shop', 'address', $post['address']);//店铺详细地址
            ConfigServer::set('order_message', 'order_contact', $post['order_contact']);//订单管理联系人
            ConfigServer::set('order_message', 'order_contact_mobile', $post['order_contact_mobile']);//订单管理联系人手机

            ConfigServer::set('trading', 'give_integral_scene', $post['give_integral_scene']);//赠送积分时机
            ConfigServer::set('trading', 'give_growth_scene', $post['give_growth_scene']);//赠送成长值时机
            $this->_success('修改成功');
        }
    }

    /**
     * 售后退款
     */
    public function policy()
    {
        $config = [
            'service' => ConfigServer::get('policy', 'service'),
            'privacy' => ConfigServer::get('policy', 'privacy'),
            'after_sale' => ConfigServer::get('policy', 'after_sale'),
            'about_us' => ConfigServer::get('policy', 'about_us'),
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }

    public function setPolicy()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('policy', 'service', $post['service']);
            ConfigServer::set('policy', 'privacy', $post['privacy']);
            ConfigServer::set('policy', 'after_sale', $post['after_sale']);
            ConfigServer::set('policy', 'about_us', $post['about_us']);

            $this->_success('修改成功');
        }
    }

    /**
     * 会员提现设置
     */
    public function withdraw()
    {
        $config = [
            'min_withdraw' => ConfigServer::get('withdraw', 'min_withdraw'),
            'max_withdraw' => ConfigServer::get('withdraw', 'max_withdraw'),
            'poundage' => ConfigServer::get('withdraw', 'poundage'),
            'type' => ConfigServer::get('withdraw', 'type') ? ConfigServer::get('withdraw', 'type') : [],
            'transfer_way' => ConfigServer::get('withdraw', 'transfer_way',1),

        ];
        $this->assign('config', $config);
        return $this->fetch();
    }


    public function setWithdraw()
    {
        $post = $this->request->post();
        if(empty($post['type'])) {
          return $this->_error('至少选择一种提现方式');
        }
        if ($post['min_withdraw'] <= 0) {
            return $this->_error('最低提现金额不能小于0');
        }
        if ($post['poundage'] > 100 || $post['poundage'] < 0) {
            return $this->_error('提现手续费须在0-100区间内');
        }
        if ($post) {
            ConfigServer::set('withdraw', 'min_withdraw', $post['min_withdraw']);//最低提现
            ConfigServer::set('withdraw', 'max_withdraw', $post['max_withdraw']);//最高提现
            ConfigServer::set('withdraw', 'poundage', $post['poundage']);//提现手续费
            ConfigServer::set('withdraw', 'type', $post['type']);//提现方式
            ConfigServer::set('withdraw', 'transfer_way', $post['transfer_way']);//微信零钱接口
            $this->_success('操作成功');
        }
    }


    /**
     * Notes: 注册设置
     * @author 段誉(2021/2/25 15:14)
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function setRegister()
    {
        if ($this->request->isAjax()){
            $post = $this->request->post();
            ConfigServer::set('register_setting', 'open', $post['open']);
            ConfigServer::set('register_setting', 'must_bind_mobile', $post['must_bind_mobile']);
            $this->_success('操作成功');
        }
        
        $this->assign('open', ConfigServer::get('register_setting', 'open', 0));
        $this->assign('must_bind_mobile', ConfigServer::get('register_setting', 'must_bind_mobile', 0));
        
        return $this->fetch('register');
    }

    /**
     * 会员提现设置
     */
    public function point()
    {
        $config = [
            'height'        => ConfigServer::get('point_way', 'height'),
            'height_limit'  => ConfigServer::get('point_way', 'height_limit'),
            'weight'        => ConfigServer::get('point_way', 'weight'),
            'weight_limit'  => ConfigServer::get('point_way', 'weight_limit'),
            'order'         => ConfigServer::get('point_way', 'order'),
            'order_limit'   => ConfigServer::get('point_way', 'order_limit'),
            'invite'        => ConfigServer::get('point_way', 'invite'),
            'invite_limit'  => ConfigServer::get('point_way', 'invite_limit'),
            'like'          => ConfigServer::get('point_way', 'like'),
            'like_limit'    => ConfigServer::get('point_way', 'like_limit'),
            'comment'       => ConfigServer::get('point_way', 'comment'),
            'comment_limit' => ConfigServer::get('point_way', 'comment_limit'),
            'share'         => ConfigServer::get('point_way', 'share'),
            'share_limit'   => ConfigServer::get('point_way', 'share_limit'),
            'video'         => ConfigServer::get('point_way', 'video'),
            'video_limit'   => ConfigServer::get('point_way', 'video_limit'),
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }


    public function setPoint()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('point_way', 'height', abIntval($post['height']));//记身高
            // ConfigServer::set('point_way', 'height_limit', abIntval($post['height_limit']));//上限次数
            ConfigServer::set('point_way', 'weight', abIntval($post['weight']));//记体重
            ConfigServer::set('point_way', 'weight_limit', abIntval($post['weight_limit']));//上限次数
            ConfigServer::set('point_way', 'order', abIntval($post['order']));//支付订单
            ConfigServer::set('point_way', 'order_limit', abIntval($post['order_limit']));//上限次数
            ConfigServer::set('point_way', 'invite', abIntval($post['invite']));//邀请好友
            ConfigServer::set('point_way', 'invite_limit', abIntval($post['invite_limit']));//上限次数
            ConfigServer::set('point_way', 'like', abIntval($post['like']));//点赞日志
            ConfigServer::set('point_way', 'like_limit', abIntval($post['like_limit']));//上限次数
            ConfigServer::set('point_way', 'comment', abIntval($post['comment']));//评论日志
            ConfigServer::set('point_way', 'comment_limit', abIntval($post['comment_limit']));//上限次数
            ConfigServer::set('point_way', 'share', abIntval($post['share']));//分享日志
            ConfigServer::set('point_way', 'share_limit', abIntval($post['share_limit']));//上限次数
            ConfigServer::set('point_way', 'video', abIntval($post['video']));//观看视频
            ConfigServer::set('point_way', 'video_limit', abIntval($post['video_limit']));//上限次数
            $this->_success('操作成功');
        }
    }
}