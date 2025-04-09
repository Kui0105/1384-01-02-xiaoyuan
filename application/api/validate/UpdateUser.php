<?php


namespace app\api\validate;

use app\common\logic\SmsLogic;
use app\common\model\Client_;
use think\Db;
use think\Validate;

class UpdateUser extends Validate
{
    protected $rule = [
        'field' => 'require|checkField',
        'value' => 'require',
    ];

    protected $message = [
        'field.require' => '参数缺失',
        'value.require' => '请填写完整',
    ];


    public function sceneSet()
    {
        $this->only(['field', 'value']);
    }

    protected function checkField($value, $rule, $data)
    {
        $allow_field = ['nickname', 'sex', 'avatar', 'mobile'];
        if (in_array($value, $allow_field)) {
            if ($value == 'mobile') {
                $result = Db::name('user')->where([['mobile','=',$data['value']],['id','<>',$data['user_id']]])->find();
                if ($result) {
                    return '此手机号已被使用';
                }
            }
            If ($value == 'nickname') {
                if (mb_strlen($data['value'], 'utf-8') > 32) {
                    return '昵称最大长度32';
                }
            }
            return true;
        }
        return '操作失败';
    }
}