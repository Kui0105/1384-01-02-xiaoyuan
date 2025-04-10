<?php


namespace app\admin\logic;

use app\admin\model\ArticleCategory;
use app\common\server\UrlServer;
use think\Db;

class ArticleCategoryLogic
{
    public static function lists($get)
    {
        $where = [];
        $where[] = ['del', '=', '0'];

        $article_category = new ArticleCategory();
        $count = $article_category->where($where)->count();
        $list = $article_category->where($where)->page($get['page'], $get['limit'])->select();

        foreach ($list as &$item) {
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            if ($item['is_show'] == 1) {
                $item['is_show_text'] = '启用';
            } else {
                $item['is_show_text'] = '停用';
            }
        }
        return ['count' => $count, 'lists' => $list];
    }

    /**
     * Desc: 添加文章分类
     * @param $post array 文章分类数据
     * @return boolean
     */
    public static function addArticleCategory($post)
    {
        $article_category = new ArticleCategory();
        $level = 0;
        if ($post['pid']) {
            $level = Db::name('article_category')->where(['id' => $post['pid']], ['del' => 0])->value('level');
        }
        $data = [
            'name'      => $post['name'],
            'pid'       => $post['pid'],
            'type'      => $post['type'],
            'is_show'   => $post['is_show'],
            'level'     => $level +1,
            'create_time' => time(),
        ];
        return $article_category->save($data);
    }

    /**
     * Desc: 编辑文章分类
     * @param $post array 文章分类数据
     * @return boolean
     */
    public static function editArticleCategory($post)
    {
        $article_category = new ArticleCategory();
        $level = 0;
        if ($post['pid']) {
            $level = Db::name('goods_category')->where(['id' => $post['pid']], ['del' => 0])->value('level');
        }
        $data = [
            'name'      => $post['name'],
            'pid'       => $post['pid'],
            'is_show'   => $post['is_show'],
            'level'     => $level +1,
            'update_time' => time(),
        ];
        return $article_category->save($data, ['id' => $post['id'], 'del' => 0]);
    }

    /**
     * Desc: 删除文章分类
     * @param $id int 文章分类id
     * @return boolean
     */
    public static function delArticleCategory($id)
    {
        $article_category = new ArticleCategory();
        $data = [
            'update_time' => time(),
            'del' => 1,
        ];
        return $article_category->save($data, ['id' => $id, 'del' => 0]);
    }

    /**
     * Desc: 获取单条文章分类
     * @param $id int 文章分类id
     * @return boolean
     */
    public static function getArticleCategory($id = 0)
    {
        $where[] = ['del', '=', 0];
        if ($id) {
            $where[] = ['id', '=', $id];
        }
        $article_category = new ArticleCategory();
        return $article_category->where($where)->column('*', 'id');
    }

    public static function switchStatus($post)
    {
        $data = [
            'is_show' => $post['status'],
            'update_time' => time(),
        ];
        return Db::name('article_category')->where(['del' => 0, 'id' => $post['id']])->update($data);
    }

    /**
     *  Desc: 获取完整的商品分类
     * @return array
     */
    public static function categoryTwoTree($type=1)
    {
        $article_category = new ArticleCategory();
        $lists = $article_category
            ->where(['del' => 0,'type'=>$type])
            ->order('sort asc,id asc')
            ->column('id,name,pid,is_show,level,sort');
        $lists = linear_to_tree($lists);
        return $lists;
    }

    public static function getTopCategory($type=1)
    {
        $article_category = new ArticleCategory();
        $lists = $article_category->field('id,name,level')->where(['del' => 0,'level'=>1,'type'=>$type])->order('sort asc,id asc')->select();
        return $lists;
    }

    /**
     * Desc: 返回该分类下有多少级子类
     * @param array $category 分类信息
     * @return string
     */
    public static function getCategoryLevel($category)
    {
        $level = 1;
        $two_id = Db::name('article_category')->where(['pid' => $category['id'], 'del' => 0])->value('id');
        if ($two_id) {
            $level = 2;
            $three_id = Db::name('article_category')->where(['pid' => $two_id, 'del' => 0])->value('id');
            if ($three_id) $level = 3;
        }
        return $level;
    }
}
