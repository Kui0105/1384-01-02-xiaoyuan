<?php



namespace app\api\logic;

use app\common\model\{IndexNotice, IndexArticle, Help};
use app\common\server\UrlServer;
use think\Db;

class IndexNoticeLogic
{
    public static function lists($type, $page, $size)
    {
        $field = 'id,title,image,create_time,visit';//content,
        $sort = 'sort asc,id desc';
        $where = ['is_show'=>1,'del'=>0];
        switch ($type){
            case 1://公告
               $model = new IndexNotice();
               break;
            case 2://时讯
                $model = new IndexArticle();
                break;
            case 3://常见问题
                $model = new Help();
                $field .= ',synopsis';//content,
                break;
            default:
                return [
                    'list' => [],
                    'count' => 0,
                    'page_no' => $page,
                    'page_size' => $size,
                    'more' => 0
                ];
        }
        $res = $model->where($where)
            ->field($field)
            ->order($sort);

        $count = $res->count();
        $list = $res->page($page, $size)->select();

        foreach ($list as &$item) {
            $item['create_time'] = date('Y-m-d ', $item['create_time']);
            $item['image'] = UrlServer::getFileUrl($item['image']);
        }

        $more = is_more($count, $page, $size);  //是否有下一页

        return [
            'list' => $list,
            'count' => $count,
            'page_no' => $page,
            'page_size' => $size,
            'more' => $more
        ];
    }

    public static function getDetail($type,$id)
    {
        $field = 'id,title,image,content,create_time,visit';
        $where = ['id'=>$id,'del'=>0];
        switch ($type){
            case 1://公告
                $model = new IndexNotice();
                break;
            case 2://时讯
                $model = new IndexArticle();
                break;
            case 3://常见问题
                $model = new Help();
                $field .= ',synopsis';//content,
                break;
            default:
                return '';
        }
        $model->where($where)->setInc('visit');
        $res = $model->where($where)
            ->field($field)
            ->find();
        if(!$res){
            return '';
        }
        $preg = '/(<img .*?src=")[^https|^http](.*?)(".*?>)/is';
        $local_url = UrlServer::getFileUrl() . '/';
        $res['content'] = preg_replace($preg, "\${1}$local_url\${2}\${3}", $res['content']);
        $res['create_time'] = date('Y-m-d ', $res['create_time']);
        $res['image'] = UrlServer::getFileUrl($res['image']);
        return $res;
    }
}