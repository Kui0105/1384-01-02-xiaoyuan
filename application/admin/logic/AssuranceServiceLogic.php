<?php


namespace app\admin\logic;
use app\common\model\AssuranceService;
use app\common\server\UrlServer;
use think\Db;
use think\facade\Cache;

class AssuranceServiceLogic
{
    /**
     * @notes 保障服务列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author ljj
     * @date 2024/7/12 上午10:30
     */
    public static function lists($get)
    {
        $where[] = ['del','=',0];

        $count = AssuranceService::where($where)->count();
        $lists = AssuranceService::where($where)
            ->page($get['page'],$get['limit'])
            ->order(['id'=>'desc'])
            ->select()
            ->toArray();

        return ['count' => $count,'list' => $lists];
    }

    /**
     * @notes 新增保障
     * @param $post
     * @return true
     * @author ljj
     * @date 2024/7/12 上午11:02
     */
    public static function add($post)
    {
        AssuranceService::create([
            'title' => $post['title'],
            'content' => $post['content']
        ]);
        return true;
    }

    /**
     * @notes 保障服务详情
     * @param $id
     * @return array
     * @author ljj
     * @date 2024/7/12 上午11:14
     */
    public static function detail($id)
    {
        return AssuranceService::where(['id' => $id])->find()->toArray();
    }

    /**
     * @notes 编辑保障服务
     * @param $post
     * @param $id
     * @return true
     * @author ljj
     * @date 2024/7/12 上午11:15
     */
    public static function edit($post,$id)
    {
        AssuranceService::update([
            'title' => $post['title'],
            'content' => $post['content']
        ],['id' => $id]);
        return true;
    }

    /**
     * @notes 删除保障服务
     * @param $id
     * @return true
     * @author ljj
     * @date 2024/7/12 上午11:16
     */
    public static function del($id)
    {
        AssuranceService::update([
            'del' => 1
        ],['id' => $id]);
        return true;
    }

    /**
     * @notes 获取所有列表
     * @param $get
     * @return array|array[]|\array[][]|\array[][][]|\array[][][][]
     * @author ljj
     * @date 2024/7/17 下午3:49
     */
    public static function getAllLists()
    {
        $lists = AssuranceService::where(['del'=>0])
            ->field('id,title')
            ->order(['id'=>'desc'])
            ->select()
            ->toArray();

        return $lists;
    }

}