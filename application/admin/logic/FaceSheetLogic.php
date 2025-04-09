<?php


namespace app\admin\logic;


use app\common\model\FaceSheetTemplate;
use Exception;
use think\Db;

class FaceSheetLogic
{
    /**
     * @notes 获取电子面单模板列表
     * @param $get
     * @return array
     * @author 张无忌
     * @date 2021/9/26 11:46
     */
    public static function lists($get)
    {
        $model = new FaceSheetTemplate();
        $count = $model->count('id');
        $lists = $model->order('id', 'desc')
            ->page($get['page'], $get['limit'])
            ->select();

        foreach ($lists as &$item) {
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['express'] = Db::name('express')->where(['id'=>$item['express_id']])->value('name') ?? '未知';
        }

        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * @notes 所有电子面单模板
     * @author 张无忌
     * @date 2021/9/27 15:40
     */
    public static function all()
    {
        $model = new FaceSheetTemplate();
        return $model->order('id', 'desc')->select();
    }

    /**
     * @notes 获取电子面单模板详细
     * @param $id
     * @return FaceSheetTemplate
     * @author 张无忌
     * @date 2021/9/26 11:51
     */
    public static function detail($id)
    {
        return FaceSheetTemplate::where(['id'=>intval($id)])->find();
    }

    /**
     * @notes 新增电子面单模板
     * @param $post
     * @return bool|string
     * @author 张无忌
     * @date 2021/9/26 11:12
     */
    public static function add($post)
    {
        try {
            FaceSheetTemplate::create([
                'express_id'  => $post['express_id'],
                'name'        => $post['name'],
                'tempid'      => $post['tempid'],
                'partner_id'  => $post['partner_id'],
                'partner_key' => $post['partner_key'],
                'net'         => $post['net'],
                'pay_type'    => $post['pay_type'],
                'create_time' => time(),
                'update_time' => time()
            ]);

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @notes 编辑电子面单模板
     * @param $post
     * @return bool|string
     * @author 张无忌
     * @date 2021/9/26 11:49
     */
    public static function edit($post)
    {
        try {
            FaceSheetTemplate::update([
                'express_id'  => $post['express_id'],
                'name'        => $post['name'],
                'tempid'      => $post['tempid'],
                'partner_id'  => $post['partner_id'],
                'partner_key' => $post['partner_key'],
                'net'         => $post['net'],
                'pay_type'    => $post['pay_type'],
                'create_time' => time(),
                'update_time' => time()
            ], ['id'=>$post['id']]);

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @notes 删除电子面单模板
     * @param $id
     * @return string
     * @author 张无忌
     * @date 2021/9/28 15:38
     */
    public static function del($id)
    {
        try {
            FaceSheetTemplate::destroy(intval($id));
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}