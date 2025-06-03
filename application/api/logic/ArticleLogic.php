<?php

namespace app\api\logic;

use app\common\logic\LogicBase;
use app\common\model\ArticleCategory;
use app\common\model\Article;
use app\common\server\UrlServer;
use think\Db;
use think\facade\Cache;

class ArticleLogic extends LogicBase{
    public static function lists($type,$get, $page, $size)
    {
        $where[] = [
            ['del', '=', 0],
            ['is_show', '=', 1],
            ['type','=',$type],
        ];
        $sort = '';
        if (!empty($get['cid'])) {
            $son_cid = ArticleCategory::where('pid',$get['cid'])->column('id');
            $all_id = array_merge([$get['cid']],$son_cid);
            $where[] = ['cid', 'in', $all_id];
        }

        if (isset($get['keyword']) && $get['keyword']) {
            $where[] = ['title', 'like', '%' . $get['keyword'] . '%'];
        }

        if(isset($get['sort_time']) && $get['sort_time']){
            $sort = 'create_time '.$get['sort_time'];
        }

        if(isset($get['sort_visit']) && $get['sort_visit']){
            $sort = 'visit '.$get['sort_visit'];
        }

        $res = DB::name('article')
            ->where($where)
            ->field('id,title,create_time,visit,image,small_type,content')
            ->order($sort);

        $count = $res->count();
        $article = $res->page($page, $size)->select();

        foreach ($article as &$item) {
            $item['create_time'] = date('Y-m-d H:i', $item['create_time']);
            $item['image'] = UrlServer::getFileUrl($item['image']);
        }

        $more = is_more($count, $page, $size);
        return [
            'list' => $article,
            'count' => $count,
            'page_no' => $page,
            'page_size' => $size,
            'more' => $more
        ];
    }

    /**
     *  Desc: 获取完整的商品分类
     * @return array
     */
    public static function categoryTwoTree($type)
    {
        $type = $type == 1?1:2;
        $article_category = new ArticleCategory();
        $lists = $article_category
            ->where(['del' => 0,'type'=>$type])
            ->order('sort asc,id asc')
            ->column('id,name,pid,is_show,level,sort');
        $lists = linear_to_tree($lists);
        return $lists;
    }

    public static function getTwoCategory($type)
    {
        $type = $type == 1?1:2;
        $arr = [];
        $article_category = new ArticleCategory();
        $one_level = $article_category->field('id,name,level')->where(['pid'=>0,'type'=>$type,'del'=>0,'is_show'=>1])->select()->toArray();
        $two_level = $article_category->where(['type'=>$type,'del'=>0,'is_show'=>1])->where('pid','<>',0)->field('id,name,level,pid')->select()->toArray();
        $son_arr = [];
        foreach ($two_level as $t){
            $son_arr[$t['pid']][] = $t;
        }
        if(empty($one_level)){
            return $arr;
        }
        foreach ($one_level as $k => &$v){
            if(empty($son_arr[$v['id']])){
                unset($one_level[$k]);
                continue;
            }
            $v['child'] = $son_arr[$v['id']];
        }
        return array_merge($one_level);
    }

    public static function getArticleDetail($id,$user_id)
    {
        DB::name('article')
            ->where(['id' => $id, 'del' => 0])
            ->setInc('visit');

        $res = DB::name('article')
            ->where(['del' => 0, 'id' => $id])
            ->field('id,title,image,visit,create_time,content,small_type,type,collect_num,foot_img,foot_url')
            ->find();

        if(!$res){
            return '';
        }

        $preg = '/<img.*?src="((?!(https|http)).*?)".*?\/?>/i';
        $local_url = UrlServer::getFileUrl();
        $res['content'] = preg_replace($preg, '<img src="' . $local_url . '${1}" />', $res['content']);
        $res['create_time'] = date('Y-m-d H:i', $res['create_time']);
        $res['image'] = UrlServer::getFileUrl($res['image']);
        $res['foot_img'] = UrlServer::getFileUrl($res['foot_img']);
        $res['is_collect'] = false;
        if($res['small_type'] == 2){
            $res['content'] = addImgDomain($res['content']);
        }
        if($res['type'] == 2){

            if($user_id){
                $res['is_collect'] = Db::name('article_collect')->where(['aid'=>$id,'user_id'=>$user_id])->count()?true:false;
            }
        }
        return $res;
    }

    public static function ACollect($user_id,$id)
    {
        $lockKey = 'article_collect_' . $user_id.'_'.$id;
        if (Cache::get($lockKey)) {
            self::$error = '操作频繁';
            return false;
        } else {
            Cache::set($lockKey, 1, 1);
        }
        $article_type = Article::where('id',$id)->value('type');
        if(!$article_type){
            self::$error = '文章不存在';
            return false;
        }
        $collect_id = Db::name('article_collect')->where(['aid'=>$id,'user_id'=>$user_id])->value('id');
        if($collect_id){
            Db::name('article_collect')->where('id',$collect_id)->delete();
            Article::where('id',$id)->setDec('collect_num');
            $res = '已取消收藏';
        }else{
            $time = time();
            Db::name('article_collect')->insert([
                'type'      => $article_type,
                'user_id'   => $user_id,
                'aid'       => $id,
                'create_time' => $time,
            ]);
            Article::where('id',$id)->setInc('collect_num');
            $res = '已收藏';
        }
        return $res;
    }
}