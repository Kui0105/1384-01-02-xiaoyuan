<?php


namespace app\admin\logic;

use app\common\model\IndexNotice;
use app\common\server\UrlServer;
use think\Db;

class IndexNoticeLogic
{

    public static function lists($get)
    {
        $notice = new IndexNotice();
        $where = [];
        $where[] = ['del', '=', '0'];
        if (isset($get['is_show']) && $get['is_show'] != '') {
            $where[] = ['is_show', '=', $get['is_show']];
        }

        if (isset($get['title']) && $get['title']) {
            $where[] = ['title', 'like', '%' . $get['title'] . '%'];
        }

        $notice_count = $notice->where($where)->count();
        $notice_list = $notice->where($where)->page($get['page'], $get['limit'])->order('sort asc,id desc')->select();

        foreach ($notice_list as &$item) {
            $item['image'] = UrlServer::getFileUrl($item['image']);
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            if ($item['is_show'] == 1) {
                $item['is_show_text'] = '显示';
            } else {
                $item['is_show_text'] = '隐藏';
            }
        }
        return ['count' => $notice_count, 'lists' => $notice_list];
    }


    public static function addNotice($post)
    {
        $notice = new IndexNotice();
        $data = [
            'title' => $post['title'],
            'image' => $post['image'],
            'content' => $post['content'],
            'is_show' => $post['is_show'],
            'create_time' => time(),
            'sort' => $post['sort'],
            'del' => 0
        ];
        return $notice->save($data);
    }

    public static function editNotice($post)
    {
        $notice = new IndexNotice();
        $data = [
            'title' => $post['title'],
            'image' => $post['image'],
            'content' => $post['content'],
            'is_show' => $post['is_show'],
            'update_time' => time(),
            'sort' => $post['sort'],
        ];
        return $notice->save($data, ['id' => $post['id'], 'del' => 0]);
    }

    public static function getNotice($id)
    {
        $notice = new IndexNotice();
        $detail =  $notice::get(['id' => $id, 'del' => 0]);
        $detail['abs_image'] = UrlServer::getFileUrl($detail['image']);
        return $detail;
    }

    public static function delNotice($id)
    {
        $notice = new IndexNotice();

        $data = [
            'del' => time(),
            'update_time' => time()
        ];
        return $notice->save($data, ['id' => $id, 'del' => 0]);
    }

    public static function switchStatus($post)
    {
        $notice = new IndexNotice();
        $data = [
            'is_show' => $post['is_show'],
            'update_time' => time(),
        ];
        return $notice->where(['del' => 0, 'id' => $post['id']])->update($data);
    }

}