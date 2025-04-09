<?php



namespace app\admin\validate;


use think\Validate;

class CommentHelper extends Validate
{
    protected $rule = [
        'goods_id' => 'require',
        'avatar' => 'require',
        'nickname' => 'require',
        'level' => 'require',
        'comment_time' => 'require|checkTime',
        'score' => 'require',
        'comment' => 'require',
    ];

    protected $message = [
        'goods_id.require' => '商品参数缺失',
        'avatar.require' => '请选择会员头像',
        'nickname.require' => '请填写昵称',
        'level.require' => '请选择会员等级',
        'comment_time.require' => '请选择评价时间',
        'score.require' => '请选择评价等级',
        'comment.require' => '请填写评价内容',
    ];


    protected function checkTime($value, $rule, $data)
    {
       if (strtotime($value) > time()) {
           return '不可大于当前时间';
       }
       return true;
    }

}