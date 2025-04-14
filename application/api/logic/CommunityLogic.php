<?php

namespace app\api\logic;

use app\common\logic\LogicBase;
use app\common\model\{Community, User,CommunityComment};
use think\Db;
use think\facade\Cache;

class CommunityLogic extends LogicBase{

    public static function add($post,$user_id)
    {
        $insert_data = [
            'type' => 1,
            'user_id'   => $user_id,
            'status'    => 1,
            'name'      => $post['name']??'',
            'content'   => $post['content']??'',
            'imgs'      => $post['imgs']?implode(',',clearDomain(explode(',',$post['imgs']))):'',
            'latitude'  => $post['latitude'],
            'longtude'  => $post['longtude'],
            'address'   => $post['address'],
            'create_time'   => time(),
        ];
        Community::insert($insert_data);
        return true;
    }


    public static function ComList($user_id,$get,$page,$size,$self = false){
        $where = [];
        $where[] = ['c.status','=',1];
        $where[] = ['c.del','=',0];
        if($self){
            $where[] = ['c.user_id','=',$user_id];
        }
        $com = new Community();

        //关键词搜索
        if(isset($get['keyword']) && $get['keyword']){
            $where[] = [ 'c.name', 'like', "%{$get['keyword']}%" ];
        }

        $com_count = $com->alias('c')
            ->where($where)
            ->count();

        $com_list = $com->alias('c')
            ->leftJoin('user u','u.id = c.user_id')
            ->where($where)
            ->page($page,$size)
            ->order('c.id desc')
            ->field('c.id,c.name,c.content,c.imgs,c.user_id,u.avatar,u.nickname,c.like_num,c.comment_num,c.create_time,c.address')
            ->select();

        foreach ($com_list as &$v){
            $v['is_like'] = false;
            $v['avatar'] = addImgDomain($v['avatar']);
            if($user_id){
                $v['is_like'] = Db::name('community_like')->where(['user_id'=>$user_id,'com_id'=>$v['id']])->count()?true:false;
            }
        }
        $more = is_more($com_count,$page,$size);  //是否有下一页

        $data = [
            'list'          => $com_list,
            'page_no'       => $page,
            'page_size'     => $size,
            'count'         => $com_count,
            'more'          => $more
        ];
        return $data;

    }

    public static function ComDetail($user_id,$id)
    {
        $detail = Community::where('id',$id)->findOrEmpty()->toArray();
        if(!$detail){
            self::$error = '帖子不存在';
            return false;
        }
        if($detail['del'] || $detail['status']!=1){
            self::$error = '帖子已删除';
            return false;
        }
        $detail['user_info'] = User::where('id',$detail['user_id'])->field('nickname,avatar,id')->findOrEmpty()->toArray();
        $detail['is_like'] = false;
        if($user_id){
            $detail['is_like'] = Db::name('community_like')->where(['user_id'=>$user_id,'com_id'=>$id])->count()?true:false;
        }
        return $detail;
    }

    public static function ComLike($user_id,$id)
    {
        $lockKey = 'com_like_' . $user_id.'_'.$id;
        if (Cache::get($lockKey)) {
            self::$error = '操作频繁';
            return false;
        } else {
            Cache::set($lockKey, 1, 1);
        }
        $if_count = Community::where('id',$id)->count();
        if(!$if_count){
            self::$error = '帖子不存在';
            return false;
        }
        $like_id = Db::name('community_like')->where(['com_id'=>$id,'user_id'=>$user_id])->value('id');
        if($like_id){
            Db::name('community_like')->where('id',$like_id)->delete();
            Community::where('id',$id)->setDec('like_num');
            $res = '已取消点赞';
        }else{
            $time = time();
            Db::name('community_like')->insert([
                'user_id'   => $user_id,
                'com_id'    => $id,
                'create_time' => $time,
            ]);
            Community::where('id',$id)->setInc('like_num');
            $res = '已点赞';
        }
        return $res;
    }

    public static function addComment($post,$user_id)
    {
        $id = $post['id']??0;
        $comment_id = $post['comment_id']??0;
        $pid = $post['pid']??0;
        $content = $post['content']??'';
        if(!$id){
            return '帖子未知';
        }
        if(!$content){
            return '请填写评论';
        }
        $lockKey = 'com_comment_' . $user_id.'_'.$id;
        if (Cache::get($lockKey)) {
            self::$error = '操作频繁';
            return false;
        } else {
            Cache::set($lockKey, 1, 1);
        }
        $detail = Community::where('id',$id)->findOrEmpty()->toArray();
        if(!$detail){
            return '帖子不存在';
        }
        if($detail['del'] || $detail['status']!=1){
            return '帖子已删除';
        }
        $p_user_id = $com_c_id = 0;
        if($pid){
            $p_data = Db::name('community_comment')->where('id',$pid)->field('user_id,del,com_c_id')->find();
            if(!$p_data || $p_data['del']){
                return '该评论已删除';
            }
            if(!$comment_id){
                return '主评论id缺失';
            }
            if($p_data['com_c_id']){
                if($comment_id != $p_data['com_c_id']){
                    return '评论下级不匹配';
                }
            }

            $p_user_id = $p_data['user_id'];
            $com_c_id = $comment_id;
        }
        $time = time();
        $insert_data = [
            'com_id'    => $id,
            'com_c_id'  => $com_c_id,
            'pid'       => $pid,
            'user_id'   => $user_id,
            'p_user_id' => $p_user_id,
            'content'   => $content,
            'create_time'   => $time,
        ];
        Db::name('community_comment')->insert($insert_data);
        Community::where('id',$id)->setInc('comment_num');
        return true;
    }

    public static function CommentLists($user_id,$id,$comment_id,$page,$size)
    {
        $com = new CommunityComment();
        if($comment_id){
            $where = ['c.del'=>0,'c.com_id'=>$id,'c.com_c_id'=>$comment_id];
            $order = 'id desc';
        }else{
            $where = ['c.del'=>0,'c.com_id'=>$id];
            $order = 'id asc';
        }

        //一级评论列表
        $com_count = $com->alias('c')
            ->where($where)
            ->count();

        $com_list = $com->alias('c')
            ->leftJoin('user u','u.id = c.user_id')
            ->where($where)
            ->page($page,$size)
            ->order($order)
            ->field('c.id,c.content,c.create_time,u.avatar,u.nickname,c.p_user_id')
            ->select();

        foreach ($com_list as &$v){
            $v['avatar'] = addImgDomain($v['avatar']);
            $v['reply'] = $v['p_user_id']?User::where('id',$v['p_user_id'])->field('avatar,nickname')->findOrEmpty()->toArray():[];
            if(!$comment_id){
                $son_data = $com->alias('c')->leftJoin('user u','u.id = c.user_id')->where(['c.del'=>0,'c.com_c_id'=>$v['id']])->field('c.id,c.content,c.create_time,u.avatar,u.nickname,c.p_user_id')->order('id asc')->limit(3)->select()->toArray();
                foreach ($son_data as &$user){
                    $user['avatar'] = addImgDomain($user['avatar']);
                    $user['reply'] = User::where('id',$user['p_user_id'])->field('avatar,nickname')->findOrEmpty()->toArray();
                }
                $v['child'] = $son_data;
            }



        }

        $more = is_more($com_count,$page,$size);  //是否有下一页
        $data = [
            'list'          => $com_list,
            'page_no'       => $page,
            'page_size'     => $size,
            'count'         => $com_count,
            'more'          => $more
        ];
        return $data;
    }

    public static function ComDel($user_id,$id)
    {
        $lockKey = 'com_del_' . $user_id.'_'.$id;
        if (Cache::get($lockKey)) {
            return '操作频繁';
            return false;
        } else {
            Cache::set($lockKey, 1, 1);
        }
        $if_count = Community::where(['id'=>$id,'user_id'=>$user_id,'del'=>0])->count();
        if(!$if_count){
            return  '帖子不存在';
        }
        $time = time();
        Community::where('id',$id)->update(['del'=>$time]);
        return true;
    }
}