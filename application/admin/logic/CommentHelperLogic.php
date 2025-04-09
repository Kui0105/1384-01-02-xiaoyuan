<?php

namespace app\admin\logic;


use app\common\model\Goods;
use app\common\server\UrlServer;
use think\Db;

class CommentHelperLogic
{
    /**
     * @notes 商品列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author 段誉
     * @date 2022/1/12 11:00
     */
    public static function lists($get)
    {
        $where = [];
        $where[] = ['del', '=', '0'];

        if (isset($get['status']) && $get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $where[] = ['name', 'like', '%' . $get['keyword'] . '%'];
        }
        if (isset($get['category_id']) && $get['category_id'] != '') {
            $where[] = ['first_category_id|second_category_id|third_category_id', '=', $get['category_id']];
        }

        $count = Db::name('goods')->where($where)->count();

        $lists = Db::name('goods')
            ->field('*,virtual_sales_sum+sales_sum as total_sales_sum')
            ->where($where)
            ->withAttr('comment_count', function ($value,$data){
                return Db::name('goods_comment')->where(['goods_id' => $data['id']])->count();
            })
            ->page($get['page'], $get['limit'])
            ->order('id desc')
            ->select();

        $goods_category = Db::name('goods_category')->where(['del' => 0])->column('name', 'id');

        foreach ($lists as &$item) {
            $item['cate_name'] = GoodsLogic::getCateName($goods_category, $item);
            $item['price'] = '￥' . $item['min_price'];
            if ($item['spec_type'] == 2 && $item['max_price'] !== $item['min_price']) {
                $item['price'] = '￥' . $item['min_price'] . '~' . '￥' . $item['max_price'];
            }
            $item['create_time_desc'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['image'] = UrlServer::getFileUrl($item['image']);
            $item['status_desc'] = Goods::getStatusDesc($item['status']);
        }

        return ['count' => $count, 'list' => $lists];
    }


    /**
     * @notes 添加虚拟评论
     * @param $post
     * @return bool|string
     * @author 段誉
     * @date 2022/1/12 11:00
     */
    public static function addComment($post)
    {
        try {
            $goods_item = Db::name('goods_item')->where(['goods_id'=> $post['goods_id']])->find();

            if (empty($goods_item)) {
                throw new \Exception('当前商品规格参数错误');
            }

            $virtual_data = [
                'sn' => create_user_sn(),
                'nickname' => $post['nickname'],
                'avatar' => $post['avatar'],
                'comment_time' => strtotime($post['comment_time']),
                'level' => $post['level'],
            ];

            $data = [
                'goods_id' => $post['goods_id'],
                'item_id' => $goods_item['id'],
                'user_id' => 0,
                'order_goods_id' => 0,
                'goods_comment' => $post['score'],
                'service_comment' => $post['score'],
                'express_comment' => $post['score'],
                'description_comment' => $post['score'],
                'comment' => $post['comment'],
                'create_time' => time(),
                'virtual_data' => json_encode($virtual_data, JSON_UNESCAPED_UNICODE)
            ];
            $comment_id = Db::name('goods_comment')->insertGetId($data);

            if (!empty($post['comment_image'])) {
                foreach ($post['comment_image'] as $image_val) {
                    $image[] = ['uri' => $image_val, 'goods_comment_id' => $comment_id];
                }
                Db::name('goods_comment_image')->insertAll($image);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}