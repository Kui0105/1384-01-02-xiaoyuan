<?php
namespace app\admin\controller;

use app\admin\logic\SelffetchShopLogic;
use app\admin\logic\UserLevelApplyLogic;
use think\facade\View;

class UserLevelApply extends AdminBase
{
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = UserLevelApplyLogic::lists($get);
            return $this->_success('获取成功', $lists);
        }
        return view('', ['area_lists' => json_encode(SelffetchShopLogic::getAreaLists(), JSON_UNESCAPED_UNICODE)]);
    }

    public function detail()
    {
        $id = $this->request->get('id');
        View::assign('detail', UserLevelApplyLogic::detail($id));
        return view();
    }

    public function audit($id)
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = UserLevelApplyLogic::audit($post);
            if ($res === false) {
                return $this->_error(UserLevelApplyLogic::getError());
            }
            return $this->_success('操作成功');
        }
        View::assign('radio_data', UserLevelApplyLogic::radio_data($id));
        return view();
    }
}