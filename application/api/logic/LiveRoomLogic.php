<?php



namespace app\api\logic;

use app\common\server\WeChatServer;
use EasyWeChat\Factory;

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
     * @author heshihu
     * @date 2021/10/14 10:08
     */
    public static function lists($page, $size)
    {

        $config = WeChatServer::getMnpConfig();
        $app = Factory::miniProgram($config);
        $result = $app->live->getRooms($page-1, $size);

        if ($result['errcode'] != 0) {
            return $result['errcode'] . '：' . $result['errmsg'];
        }

        $liveStatus = [101=>'直播中', 102=>'未开始', 103=>'已结束', 104=>'禁播', 105=>'暂停', 106=>'异常', 107=>'已过期'];

        $data = [];
        foreach ($result['room_info'] as $item) {
            $data[] = [
                'name'        => $item['name'],
                'roomid'      => $item['roomid'],
                'cover_img'   => $item['cover_img'],
                'anchor_name' => $item['anchor_name'],
                'status'      => $item['live_status'],
                'live_status' => $liveStatus[$item['live_status']],
                'goods'       => count($item['goods']),
                'start_time'  => date('Y-m-d H:i:s', $item['start_time']),
                'end_time'    => date('Y-m-d H:i:s', $item['end_time'])
            ];
        }

        $more = is_more($result['total'], $page, $size);  //是否有下一页

        return [
            'list' => $data,
            'count' => $result['total'],
            'page_no' => $page,
            'page_size' => $size,
            'more' => $more
        ];
    }

}