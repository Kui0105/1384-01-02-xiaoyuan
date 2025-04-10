<?php


namespace app\admin\logic;

use app\common\model\IndexArticle;
use app\common\server\UrlServer;
use think\Db;

class IndexArticleLogic
{

    public static function lists($get)
    {
        $article = new IndexArticle();
        $where = [];
        $where[] = ['del', '=', '0'];
        if (isset($get['is_show']) && $get['is_show'] != '') {
            $where[] = ['is_show', '=', $get['is_show']];
        }

        if (isset($get['title']) && $get['title']) {
            $where[] = ['title', 'like', '%' . $get['title'] . '%'];
        }

        $article_count = $article->where($where)->count();
        $article_list = $article->where($where)->page($get['page'], $get['limit'])->order('sort asc,id desc')->select();

        foreach ($article_list as &$item) {
            $item['image'] = UrlServer::getFileUrl($item['image']);
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            if ($item['is_show'] == 1) {
                $item['is_show_text'] = '显示';
            } else {
                $item['is_show_text'] = '隐藏';
            }
        }
        return ['count' => $article_count, 'lists' => $article_list];
    }


    public static function addArticle($post)
    {
        $article = new IndexArticle();
        $data = [
            'title' => $post['title'],
            'image' => $post['image'],
            'content' => $post['content'],
            'is_show' => $post['is_show'],
            'create_time' => time(),
            'sort' => $post['sort'],
            'del' => 0
        ];
        return $article->save($data);
    }

    public static function editArticle($post)
    {
        $article = new IndexArticle();
        $data = [
            'title' => $post['title'],
            'image' => $post['image'],
            'content' => $post['content'],
            'is_show' => $post['is_show'],
            'update_time' => time(),
            'sort' => $post['sort'],
        ];
        return $article->save($data, ['id' => $post['id'], 'del' => 0]);
    }

    public static function getArticle($id)
    {
        $article = new IndexArticle();
        $detail =  $article::get(['id' => $id, 'del' => 0]);
        $detail['abs_image'] = UrlServer::getFileUrl($detail['image']);
        return $detail;
    }

    public static function delArticle($id)
    {
        $article = new IndexArticle();

        $data = [
            'del' => time(),
            'update_time' => time()
        ];
        return $article->save($data, ['id' => $id, 'del' => 0]);
    }

    public static function switchStatus($post)
    {
        $article = new IndexArticle();
        $data = [
            'is_show' => $post['is_show'],
            'update_time' => time(),
        ];
        return $article->where(['del' => 0, 'id' => $post['id']])->update($data);
    }

}