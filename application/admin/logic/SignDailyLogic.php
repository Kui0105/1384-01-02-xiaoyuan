<?php


namespace app\admin\logic;
use app\admin\model\Coupon;
use app\common\server\UrlServer;

use app\common\server\ConfigServer;
use think\Db;
use think\Exception;

class SignDailyLogic
{

    /**
     * 签到设置列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function lists()
    {
        $where[] = ['type', '=',2];
        $where[] = ['del','=',0];
        $count =  Db::name('sign_daily')->where($where)->count();
        $lists = Db::name('sign_daily')->where($where)->select();
        foreach ($lists as $key => $sign){
            $tips = '';
            if(1 == $sign['integral_status'] && $sign['integral'] > 0){
                $tips.= '赠送'.$sign['integral'].'小元币；';
            }
            if(1 == $sign['growth_status'] && $sign['growth'] > 0){
                $tips.= '赠送'.$sign['growth'].'成长值';
            }
            if(1 == $sign['coupon_status'] && $sign['coupon_id']){
                $coupon_name = Coupon::where('id','in',$sign['coupon_id'])->column('name');
                $coupon_name_str = implode(',',$coupon_name);
                $tips.= '赠送优惠券【'.$coupon_name_str.'】';
            }
            $lists[$key]['award_tips'] = $tips;

        }
        return ['count' => $count,'list' => $lists];

    }
    /**
     * 签到记录列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function record($get){
        $where = [];

        if (isset($get['keyword']) && $get['keyword']){
            $where[] = [$get['type'],'like','%'.$get['keyword'].'%'];
        }


        $count = Db::name('user_sign us')
            ->where(['us.del'=>0,'u.del'=>0])
            ->where($where )
            ->join('user u','u.id = us.user_id')
            ->count();
        $lists = Db::name('user_sign us')
            ->where(['us.del'=>0,'u.del'=>0])
            ->where($where )
            ->join('user u','u.id = us.user_id')
            ->field('us.user_id,sn,nickname,avatar,mobile,sex,u.create_time ,days,integral,
            continuous_integral, sign_time,mobile,us.sign_time,us.coupon_id')//continuous_growth,growth,
            ->order('us.id desc')
            ->page($get['page'], $get['limit'])
            ->select();
        foreach ( $lists as &$item){
            switch ($item['sex']){
                case 1:
                    $item['sex'] = '男';
                    break;
                case 2:
                    $item['sex'] = '女';
                    break;
                default:
                    $item['sex'] = '未知';
            }
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['sign_time']   = date('Y-m-d H:i:s', $item['sign_time']);
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            $item['coupon_txt'] = '';
            if($item['coupon_id']){
                $coupon_name = Coupon::where('id','in',$item['coupon_id'])->column('name');
                $coupon_name_str = implode(',',$coupon_name);
                $item['coupon_txt'] =  '；赠送优惠券【'.$coupon_name_str.'】';
            }
        }
        return ['count' => $count,'list' => $lists];
    }


    /**
     * 添加连续签到奖励
     * @param $post
     */
    public static function add($post)
    {
        $data = [
            'type'                => '2',
            'days'                => $post['days'],
            'integral'            => $post['integral'],
            'integral_status'     => $post['integral_status'],
            'growth'              => '',//$post['growth'],
            'growth_status'       => 0,//$post['growth_status'],
            'coupon_id'           => $post['coupon_id'],
            'coupon_status'       => $post['coupon_status'],
            'del'                 => 0,
            'create_time'         => time()
        ];
        return Db::name('sign_daily')->insert($data);
    }

    /**
     * 获取连续签到奖励
     * @param $id
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSignDaily($id)
    {
        return Db::name('sign_daily')->where(['id' =>$id])->find();
    }


    /**
     * 编辑连续签到奖励
     * @param $post
     * @param $id
     * @return bool
     */
    public static  function edit($post,$id)
    {
        $data = [
            'type'                => '2',
            'days'                => $post['days'],
            'integral'            => $post['integral'],
            'integral_status'     => $post['integral_status'],
            'growth'              => '',//$post['growth'],
            'growth_status'       => 0,//$post['growth_status'],
            'coupon_id'           => $post['coupon_id'],
            'coupon_status'       => $post['coupon_status'],
            'del'                 => 0,
            'update_time'         => time()
            ];

        return Db::name('sign_daily')->where(['id' =>$id])->update($data);

    }

    /**
     * 删除连续签到奖励列表
     * @param $id
     * @return int|string
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function del($id)
    {
        $data = [
            'id'           =>$id,
            'del'          =>1,
            'update_time'  =>time()
        ];
        $result = Db::name('sign_daily')->where(['id' =>$id])->update($data);
        return $result;
    }

    /**
     * 设置签到
     * @param $post
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function  setSignRule($post)
    {

        $data = [
            'type'             =>1,
            'days'             => 0,
            'integral'         =>$post['integral'],
            'growth'           =>$post['growth'],
            'integral_status'  =>$post['integral_status'],
            'growth_status'    =>$post['growth_status'],
            'create_time'      =>time(),
            'del'              =>0,

        ];

        $info = [
            'type'             =>1,
            'days'             => 0,
            'integral'         =>$post['integral'],
            'growth'           =>$post['growth'],
            'integral_status'  =>$post['integral_status'],
            'growth_status'    =>$post['growth_status'],
            'update_time'      =>time(),
            'del'              =>0,

        ];



        $name = Db::name('sign_daily')->where(['type' =>1])->find();
        if (empty($name)){
            Db::name('sign_daily')->insert($data);
        }else{
            Db::name('sign_daily')->where(['type' =>1])->update($info);
        }


         ConfigServer::set('sign_rule','instructions',$post['instructions']);

        return true;
    }

    /**
     * 获取签到规则说明
     * @return array
     */
    public static function getSignRule()
    {


        $data = Db::name('sign_daily')->where(['type' =>1])->find();
        $config = [
            'instructions' => ConfigServer::get('sign_rule','instructions'),
            'dailySign' => $data

        ];



        return $config;

    }



}