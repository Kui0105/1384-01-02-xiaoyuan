<?php


namespace app\admin\controller;

use app\admin\logic\BabyCheckLogic;

class BabyCheck extends AdminBase
{

    /**
     * 骨龄测评列表
     * @return mixed
     */
    public function bone_lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $this->_success('获取成功', BabyCheckLogic::lists($get,4));
        }
        return $this->fetch();
    }

    public function upload_pdf($id)
    {
        if($this->request->isAjax()){
            $post = $this->request->post();
            $result = BabyCheckLogic::upload_pdf($post);
            if($result === true){
                $this->_success('保存成功');
            }
            return $this->_error($result);
        }
        $this->assign('id',$id);
        return $this->fetch();
    }
}