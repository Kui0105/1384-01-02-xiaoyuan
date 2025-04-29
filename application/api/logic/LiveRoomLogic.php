<?php



namespace app\api\logic;

use app\common\model\Live;
use app\common\server\WeChatServer;
use EasyWeChat\Factory;
use think\Db;

/**
 * 直播 逻辑层
 * Class LiveRoom
 * @package app\api\controller
 */
class LiveRoomLogic
{
    /**
     * @notes 获取直播信息列表
     * @param $page
     * @param $size
     * @return array|string
     */
    public static function lists($page, $size)
    {
        $res = DB::name('live')
            // ->where($where)
            ->field('create_time,update_time',true)
            ->order(['id' => 'desc']);

        $count = $res->count();
        $article = $res->page($page, $size)->select();

        foreach ($article as &$item) {
            $item['image'] = addImgDomain($item['image']);
        }

        $more = is_more($count, $page, $size);
        return [
            'list' => $article,
            'count' => $count,
            'page_no' => $page,
            'page_size' => $size,
            'more' => $more
        ];

        // $config = WeChatServer::getMnpConfig();
        // $app = Factory::miniProgram($config);
        // $result = $app->live->getRooms($page-1, $size);

        // if ($result['errcode'] != 0) {
        //     return $result['errcode'] . '：' . $result['errmsg'];
        // }

        // $liveStatus = [101=>'直播中', 102=>'未开始', 103=>'已结束', 104=>'禁播', 105=>'暂停', 106=>'异常', 107=>'已过期'];

        // $data = [];
        // foreach ($result['room_info'] as $item) {
        //     $data[] = [
        //         'name'        => $item['name'],
        //         'roomid'      => $item['roomid'],
        //         'cover_img'   => $item['cover_img'],
        //         'anchor_name' => $item['anchor_name'],
        //         'status'      => $item['live_status'],
        //         'live_status' => $liveStatus[$item['live_status']],
        //         'goods'       => count($item['goods']),
        //         'start_time'  => date('Y-m-d H:i:s', $item['start_time']),
        //         'end_time'    => date('Y-m-d H:i:s', $item['end_time'])
        //     ];
        // }

        // $more = is_more($result['total'], $page, $size);  //是否有下一页

        // return [
        //     'list' => $data,
        //     'count' => $result['total'],
        //     'page_no' => $page,
        //     'page_size' => $size,
        //     'more' => $more
        // ];
    }

    //直播列表同步
    public static function live_update($post,$user_id){
        $is_live = Db::name('user')->where('id',$user_id)->value('is_live');
        if(!$is_live){
            return '无同步权限';
        }
        $time = time();
        $new_list = [];
        $new_list[] = [
            'description'   => $post['description']??'',
            'feedId'        => $post['feedId']??'',
            'headUrl'       => $post['headUrl']??'',
            'nickname'      => $post['nickname']??'',
            'nonceId'       => $post['nonceId']??'',
            'playbackState' => $post['playbackState']??'',
            'replayStatus'  => $post['replayStatus']??0,
            'status'        => $post['status']??0,
            'create_time'   => $time,
            'update_time'   => $time,
        ];
        if(!empty($post['otherInfos'])){
            foreach ($post['otherInfos'] as $v) {
                $new_list[] = [
                    'description'   => $v['description']??'',
                    'feedId'        => $v['feedId']??'',
                    'headUrl'       => $v['headUrl']??'',
                    'nickname'      => $v['nickname']??'',
                    'nonceId'       => $v['nonceId']??'',
                    'playbackState' => $v['playbackState']??'',
                    'replayStatus'  => $v['replayStatus']??0,
                    'status'        => $v['status']??0,
                    'create_time'   => $time,
                    'update_time'   => $time,
                ];
            }
        }
        if(empty($new_list)){
            return '同步失败(缺少更新数据)';
        }
        $new_feed_ids = array_column($new_list,'feedId');
        $have_feed_ids = Db::name('live')->where('feedId','in',$new_feed_ids)->column('id','feedId');
        if(!empty($have_feed_ids)){
            $id_to_feed = array_flip($have_feed_ids);
            foreach ($new_list as &$v) {
                if(in_array($v['feedId'],$id_to_feed)){
                    $v['id'] = $have_feed_ids[$v['feedId']];
                }
            }
        }
        (new Live())->saveAll($new_list);
        return true;
    }

}