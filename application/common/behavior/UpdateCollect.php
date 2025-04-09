<?php

namespace app\common\behavior;

use think\Db;

/**
 * 更新商品收藏夹
 * Class UpdateCollect
 * @package app\common\behavior
 */
class UpdateCollect
{
    public function run($params)
    {
        try {
            $goods_ids = $params['goods_id'] ?? '';

            if (empty($goods_ids)) {
                return true;
            }

            if (!is_array($goods_ids)) {
                $goods_ids = [$goods_ids];
            }

            $map1 = [
                ['id', 'in', $goods_ids],
                ['status', '<>', 1],
            ];
            $map2 = [
                ['id', 'in', $goods_ids],
                ['del', '=', 1],
            ];
            $goods_ids = Db::name('goods')->whereOr([$map1, $map2])->column('id');


            if (empty($goods_ids)) {
                return true;
            }

            Db::name('goods_collect')->whereIn('goods_id', $goods_ids)->delete();

            return true;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}