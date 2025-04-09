<?php

namespace app\api\logic;

use app\common\logic\LogicBase;
use app\common\logic\QrCodeLogic;
use app\common\model\Bargain;
use app\common\model\BargainLaunch;
use app\common\model\Client_;
use app\common\server\QrCodeServer;
use app\common\server\UrlServer;
use think\Db;

class ShareLogic extends LogicBase
{
    //商品分销海报
    public static function shareGoods($user_id, $goods_id, $url, $client)
    {
        $qr_code_logic = new QrCodeLogic();
        $goods = Db::name('goods')->where(['id' => $goods_id])->find();
        $result = '';
        if ($goods) {
            $user = Db::name('user')->where(['id' => $user_id])->find();

            switch ($client) {
                case Client_::mnp: //小程序
                    $url_type = 'path';
                    break;
                case Client_::oa: //公众号.
                case Client_::h5: //H5.
                    $url_type = 'url';
                    $url = url($url, '', '', true) . '?' . http_build_query(['id' => $goods_id, 'invite_code' => $user['distribution_code']]);
                    break;
                case Client_::android:
                case Client_::ios:
                    $url_type = 'url';
                    $url = url($url, '', '', true) . '?' . http_build_query(['id' => $goods_id, 'invite_code' => $user['distribution_code'], 'isapp' => 1]);
            }

            $result = $qr_code_logic->makeGoodsPoster($user, $goods, $url, $url_type);
        }
        return $result;
    }


    //获取用户分享海报
    public static function getUserPoster($user_id, $url, $client)
    {
        //判断用户是否已有生成二维码分享海报
        $user = Db::name('user')->where(['id' => $user_id])->find();

        $url_type = 'url';
        $invite_code_text = 'distribution_app_qr_code';

        if ($client == Client_::mnp || $client == Client_::oa) {
            if (empty($url)) {
                return self::dataError('参数缺失');
            }
        }

        switch ($client) {
            case Client_::mnp:
                $url_type = 'path';
                $invite_code_text = 'distribution_mnp_qr_code';
                $content = $url;
                break;
            case Client_::oa:
            case Client_::h5:
                $invite_code_text = 'distribution_h5_qr_code';
                $url = request()->domain() . $url;
                $content = $url . '?invite_code=' . $user['distribution_code'];
                break;
            case Client_::ios:
            case Client_::android:
                $content = url('index/index/app', '', '', true);
                break;
            default:
                return self::dataError('系统错误');
        }

        $qr_code_logic = new QrCodeLogic();
        $poster = $qr_code_logic->makeUserPoster($user, $content, $url_type, $client);

        if ($poster['status'] != 1) {
            return self::dataError($poster['msg']);
        }

        $poster_url = $poster['data'];
        //更新user表
        Db::name('user')->where(['id' => $user_id])->update([$invite_code_text => $poster_url]);

        return self::dataSuccess('', ['url' => UrlServer::getFileUrl($poster_url)]);
    }


    //砍价分销海报
    public static function shareBargain($user_id, $id, $url, $client)
    {
        $user = Db::name('user')->where(['id' => $user_id])->find();
        switch ($client) {
            case Client_::mnp: //小程序
                $url_type = 'path';
                break;
            case Client_::h5: //H5.
            case Client_::oa: //公众号.
                $url_type = 'url';
                $url = url($url, '', '', true) . '?' . 'id=' . $id;
                break;
            case Client_::android:
            case Client_::ios:
                $url_type = 'url';
                $url = url($url, '', '', true) . '?' . http_build_query(['id' => $id, 'isapp' => 1]);

        }
        $bargain_launch = new BargainLaunch();
        $bargain_launch = $bargain_launch->where(['id' => $id])->find()->toarray();
        $qr_code_logic = new QrCodeLogic();
        $result = $qr_code_logic->makeBargainPoster($user, $bargain_launch, $url, $url_type);
        return $result;

    }

    /**
     * @notes 获取小程序码
     * @param int $user_id
     * @param array $get
     * @author cjhao
     * @date 2021/11/25 10:52
     */
    public static function getMnQrcode(int $user_id, array $get)
    {
        $type = $get['type'] ?? 0;
        $scene = null;
        $extra = [];

        if(2 != $type) {
            //用户分销码
            $distribution_code = Db::name('user')->where(['id' => $user_id])->value('distribution_code');
            $scene = 'invite_code='.$distribution_code;

            if(1 == $type){
                $scene .= '&id='.$get['id'];
            }
        } else {
            $launch = BargainLaunch::where(['id'=>$get['id']])->value('bargain_id');
            $extra = Bargain::where(['id' => $launch])
                ->field('share_title,share_intro')
                ->find()->toArray();

            $scene = 'id='.$get['id'];
        }

        $param = [
            'page'      => $get['url'],
            'scene'     => $scene
        ];

        return QrCodeServer::makeMpWechatQrcode($param,'base64', $extra);
    }
}