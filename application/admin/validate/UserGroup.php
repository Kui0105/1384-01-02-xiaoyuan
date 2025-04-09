<?php



namespace app\admin\validate;
use think\Db;
use think\Validate;

class UserGroup extends Validate
{


    protected $rule = [
        'id'        => 'require|checkUser',
        'name'      => 'require|max:16|unique:user_group,name^del',
        'remark'    => 'max:64'

    ];

    protected $message = [
        'id.require'    => '请选择分组',
        'name.max'      => '分类名称最多16个字符',
        'name.require'  => '分组名称不能为空',
        'name.unique'   => '分组名称已存在',
        'remark.max'    => '备注最多64个字符',
    ];

    public function sceneAdd()
    {
        $this->only(['name']);
    }
    public function sceneDel()
    {
        $this->only(['id']);
    }
    public function sceneEdit()
    {
        $this->remove('id','checkUser');
    }

    protected function checkUser($value,$rule,$data){
       $user = Db::name('user')->where(['del'=>0,'group_id'=>$value])->find();
       if($user){
           return '已有会员属于该分组,不能删除';
       }
       return true;
    }


}