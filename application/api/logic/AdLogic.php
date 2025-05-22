<?php



namespace app\api\logic;

use app\common\model\Ad;
use app\common\server\UrlServer;
use think\Db;

class AdLogic
{
    public static function lists($pid, $client)
    {
        $ad_list = Db::name('ad a')
            ->join('ad_position ap', 'a.pid = ap.id')
            ->where(['pid' => $pid, 'ap.client' => $client, 'a.status' => 1, 'a.del' => 0, 'ap.status' => 1, 'ap.del' => 0])
            ->field('a.*')
            ->order(['sort' => 'desc', 'id' => 'desc'])
            ->select();

        $list = [];
        foreach ($ad_list as $key => $ad) {
            $url = $ad['link'];
            $is_tab = 0;
            $params = [];
            switch ($ad['link_type']) {
                case 1:
                    $page = Ad::getLinkPage($ad['client'], $ad['link']);
                    $url = $page['path'];
                    $is_tab = $page['is_tab'] ?? 0;
                    break;
                case 2:
                    $goods_path = Ad::getGoodsPath($ad['client']);
                    $url = $goods_path.$ad['link'];
                    $params = [
                        'id' => $ad['link'],
                    ];
                    break;
                case 4:
                case 5:
                    $goods_path = Ad::getArticlePath($ad['client']).$ad['link'];
                    $url = $goods_path;
                    $params = [
                        'id' => $ad['link'],
                    ];
                    break;
                case 6:
                    // $goods_path = Ad::getArticlePath($ad['client']).$ad['link'];
                    $url = '/pages/notice/detail?type=2&id='.$ad['link'];
                    $params = [
                        'id' => $ad['link'],
                    ];

                    break;

            }
            $list[] = [
                'image'     => UrlServer::getFileUrl($ad['image']),
                'url'       => $url,
                'link_type' => $ad['link_type'],
                'params'    => $params,
                'is_tab'    => $is_tab,
                'sort'      => $ad['sort'],
            ];
        }
        return $list;
    }
}