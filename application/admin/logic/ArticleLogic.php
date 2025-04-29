<?php


namespace app\admin\logic;

use app\admin\model\Article;
use app\admin\model\ArticleCategory;
use app\common\server\UrlServer;
use think\Db;

class ArticleLogic
{

    public static function lists($get)
    {
        $article = Db::name('article');

        $where = [];
        $where[] = ['del', '=', '0'];
        if (isset($get['type']) && $get['type']) {
            $where[] = ['type', '=', $get['type']];
        }

        if (isset($get['title']) && $get['title']) {
            $where[] = ['title', 'like', '%' . $get['title'] . '%'];
        }

        if (isset($get['cid']) && $get['cid']) {
            $son_cid = ArticleCategory::where('pid',$get['cid'])->column('id');
            $all_id = array_merge([$get['cid']],$son_cid);
            $where[] = ['cid', 'in', $all_id];
        }

        if (isset($get['small_type']) && $get['small_type']) {
            $where[] = ['small_type', '=', $get['small_type']];
        }

        $count = $article->where($where)->count();
        $list = $article->where($where)->page($get['page'], $get['limit'])->order('id desc')->select();
        foreach ($list as &$item) {
            $item['image'] = UrlServer::getFileUrl($item['image']);
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                $item['cat_name'] = ArticleCategory::where('id',$item['cid'])->value('name');
            if ($item['is_show'] == 1) {
                $item['is_show_text'] = '显示';
            } else {
                $item['is_show_text'] = '隐藏';
            }

            $item['type_txt'] = $item['small_type']==1?'文章':'视频';
        }
        return ['count' => $count, 'lists' => $list];
    }


    public static function addArticle($post)
    {
        $article = new Article();
        if($post['small_type'] == 1){
            $content = $post['content']??'';
        }else{
            $content = UrlServer::setFileUrl($post['video']);
        }

        $data = [
            'title'     => $post['title'],
            'cid'       => $post['cid'],
            'image'     => $post['image'],
            'content'   => $content,
            'is_show'   => $post['is_show'],
            'type'      => $post['type'],
            'small_type'    => $post['small_type'],
            'create_time'   => time(),
            'sort'          => $post['sort'],
            'foot_img'      => $post['foot_img']??'',
            'foot_url'      => $post['foot_url']??'',
            // 'is_notice' => $post['is_notice'],
            // 'synopsis' => $post['synopsis']
        ];
        return $article->save($data);
    }

    public static function editArticle($post)
    {
        $article = new Article();
        if($post['small_type'] == 1){
            $content = $post['content']??'';
        }else{
            $content = UrlServer::setFileUrl($post['video']);
        }
        $data = [
            'title'     => $post['title'],
            'cid'       => $post['cid'],
            'image'     => $post['image'],
            'content'   => $post['content'],
            'is_show'   => $post['is_show'],
            'update_time'   => time(),
            'sort'      => $post['sort'],
            'foot_img'      => $post['foot_img']??'',
            'foot_url'      => $post['foot_url']??'',
            // 'is_notice' => $post['is_notice'],
            // 'synopsis' => $post['synopsis']

        ];
        return $article->save($data, ['id' => $post['id'], 'del' => 0]);
    }

    public static function getArticle($id)
    {
        $article = new Article();
        $detail = $article::get(['id' => $id, 'del' => 0]);
        $detail['abs_image'] = UrlServer::getFileUrl($detail['image']);
        $detail['foot_image'] = UrlServer::getFileUrl($detail['foot_img']);
        if($detail['small_type'] == 2){
            $detail['content'] = UrlServer::getFileUrl($detail['content']);
        }
        return $detail;
    }

    public static function delArticle($id)
    {
        $article = new Article();

        $data = [
            'del' => 1,
            'update_time' => time()
        ];
        return $article->save($data, ['id' => $id, 'del' => 0]);
    }

    public static function switchStatus($post)
    {
        $data = [
            'is_show' => $post['is_show'],
            'update_time' => time(),
        ];
        return Db::name('article')->where(['del' => 0, 'id' => $post['id']])->update($data);
    }

}