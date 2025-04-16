<?php


namespace app\admin\logic;

use app\common\model\Community;
use app\common\model\CommunityComment;
use app\common\model\CommunityLike;
use app\common\model\User;
use app\common\server\UrlServer;
use think\Db;

class CommunityLogic
{

    public static function lists($get)
    {
        $community = new Community();

        $where = [];
        $where[] = ['c.del', '=', '0'];
        if (isset($get['type']) && $get['type']) {
            $where[] = ['c.type', '=', $get['type']];
        }

        if (isset($get['title']) && $get['title']) {
            $where[] = ['c.name', 'like', '%' . $get['title'] . '%'];
        }

        if (isset($get['user_name']) && $get['user_name']) {
            $where[] = ['u.nickname|u.mobile', 'like', '%' . $get['user_name'] . '%'];
        }

        if (isset($get['status']) && $get['status']) {
            $where[] = ['c.status', '=', $get['status']];
        }

        $count = $community->alias('c')
            ->leftJoin('user u','u.id = c.user_id')
            ->where($where)
            ->count();
        $list = $community->alias('c')
            ->leftJoin('user u','u.id = c.user_id')
            ->field('c.id,c.user_id,c.name,c.content,c.imgs,c.address,c.create_time,c.like_num,c.comment_num,u.avatar,u.nickname,u.mobile')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('c.id desc')
            ->select();
        foreach ($list as &$item) {
            $item['avatar'] = addImgDomain($item['avatar']);
        }
        return ['count' => $count, 'lists' => $list];
    }



    public static function editArticle($post)
    {
        $community = new Community();
        $img_str = '';
        if(!empty($post['imgs'])){
            $img_str = implode(',',clearDomain($post['imgs']));
        }
        $data = [
            'name'      => $post['name'],
            'content'   => $post['content'],
            'imgs'      => $img_str,
        ];
        return $community->save($data, ['id' => $post['id'], 'del' => 0]);
    }

    public static function getArticle($id)
    {
        $community = new Community();
        $detail = $community::get(['id' => $id, 'del' => 0]);
        $detail['user'] = User::field('nickname,avatar')->where('id',$detail['user_id'])->findOrEmpty()->toArray();
        return $detail;
    }

    public static function delArticle($id)
    {
        $community = new Community();

        $data = [
            'del' => time(),
        ];
        return $community->save($data, ['id' => $id, 'del' => 0]);
    }

    public static function switchStatus($post)
    {
        $community = new Community();
        $data = [
            'is_show' => $post['is_show'],
        ];
        return $community->where(['del' => 0, 'id' => $post['id']])->update($data);
    }

    public static function like_lists($get)
    {
        $like = new CommunityLike();
        $count = $like->alias('l')
            ->leftJoin('user u','u.id = l.user_id')
            ->where('l.com_id',$get['id'])
            ->count();
        $list = $like->alias('l')
            ->leftJoin('user u','u.id = l.user_id')
            ->field('l.id,l.create_time,u.avatar,u.nickname')
            ->where('l.com_id',$get['id'])
            ->page($get['page'], $get['limit'])
            ->order('l.id desc')
            ->select();
        foreach ($list as &$item) {
            $item['avatar'] = addImgDomain($item['avatar']);
        }
        return ['count' => $count, 'lists' => $list];
    }

    public static function comment_lists($get)
    {
        $comment = new CommunityComment();
        $where = [];
        $where[] = ['cc.del','=',0];
        if (isset($get['title']) && $get['title']) {
            $where[] = ['cc.content', 'like', '%' . $get['title'] . '%'];
        }
        $count = $comment->alias('cc')
            ->leftJoin('user u','u.id = cc.user_id')
            ->where('cc.com_id',$get['id'])
            ->where($where)
            ->count();
        $list = $comment->alias('cc')
            ->leftJoin('user u','u.id = cc.user_id')
            ->field('cc.id,cc.create_time,u.avatar,u.nickname,cc.p_user_id,cc.content')
            ->where('cc.com_id',$get['id'])
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('cc.id desc')
            ->select();
        foreach ($list as &$item) {
            $item['avatar'] = addImgDomain($item['avatar']);
            $item['p_user'] = '';
            if($item['p_user_id']){
                $item['p_user'] = User::where('id',$item['p_user_id'])->field('avatar,nickname')->findOrEmpty()->toArray();
            }
        }
        return ['count' => $count, 'lists' => $list];
    }

    public static function delComment($id)
    {
        $comment = new CommunityComment();
        $else_id = $comment->where('com_c_id',$id)->column('id');
        $ids = array_merge($else_id,[$id]);
        $time = time();
        return $comment->where('id','in',$ids)->update(['del'=>$time]);
    }

}