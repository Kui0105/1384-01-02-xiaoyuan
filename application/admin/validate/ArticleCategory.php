<?php


namespace app\admin\validate;

use think\Validate;
use think\Db;
use app\admin\logic\ArticleCategoryLogic;

class ArticleCategory extends Validate
{

    protected $rule = [
        'id'    => 'require|CheckArticle',
        'name'  => 'require|checkName',
        'pid'   => 'addPid|editPid',
        // 'is_show'=>'require'
    ];

    protected $message = [
        'id.require'    => 'id不可为空',
        'name.require'  => '分类名称不能为空！',
        // 'name.unique'   => '分类名称已存在',
        // 'is_show.require'=>'请选择显示状态'
    ];
    protected function sceneAdd()
    {
        $this->remove('id')
            ->remove('pid','editPid');
    }

    protected function sceneEdit()
    {
        $this->remove('pid','addPid')
            ->remove('id','CheckArticle');
    }

    public function sceneDel()
    {
        $this->only(['id']);
    }

    protected function CheckArticle($value, $rule, $data){
        if(\app\admin\model\Article::get(['cid'=>$value,'del'=>0])){
            return '该分类下有文章，不可删除';
        }
        return true;
    }

    /*
     * 新增时验证上级
     */
    protected function addPid($value, $rule, $data){
        if($value == 0) return true;

        $where['id'] = $value;
        $where['type'] = $data['type'];
        $where['del'] = 0;
        $article_category = Db::name('article_category')->where($where)->find();

        if($article_category)  return true;
        return '父级分类不存在，请重新选择';

    }

    /*
     * 编辑时验证上级
     */
    protected function editPid($value, $rule, $data){
        if($value == 0 ) return true;
        //验证的分类
        $category = Db::name('article_category')->where(['id'=>$data['id'],'del'=>0])->find();
        //分类的父级
        $partner = Db::name('article_category')->where(['id'=>$value,'del'=>0])->find();
        //获取该分类有多少级子类
        $level = ArticleCategoryLogic::GetCategoryLevel($category);

        if($category['id'] == $partner['id']) return '父级分类不能是自己';
        if($level == 2 && $partner) return '该分类有完整的子分类，不可修改父级';
        if($partner['level'] == 2) return '父级分类不能是第二级';

        return true;
    }

    protected  function checkName($value,$rule,$data)
    {
        if($data['id']){
            $if_name = Db::name('article_category')->where(['name'=>$value,'type'=>$data['type'],'del'=>0])->where('id','<>',$data['id'])->count();
        }else{
            $if_name = Db::name('article_category')->where(['name'=>$value,'type'=>$data['type'],'del'=>0])->count();
        }


        if($if_name){
            return '分类名称已存在';
        }
        return true;
    }

}