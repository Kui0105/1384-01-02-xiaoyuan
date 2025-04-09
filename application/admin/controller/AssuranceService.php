<?php

namespace app\admin\controller;


use app\admin\logic\AssuranceServiceLogic;

class AssuranceService extends AdminBase
{
    /**
     * @notes 保障服务列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author ljj
     * @date 2024/7/12 上午10:30
     */
    public function lists()
    {
        if ($this->request->isAjax())
        {
            $get = $this->request->get();
            $list = AssuranceServiceLogic::lists($get);
            $this->_success('',$list);
        }
        return $this->fetch();
    }

    /**
     * @notes 新增保障
     * @return mixed
     * @author ljj
     * @date 2024/7/12 上午11:03
     */
    public function add()
    {
        if ($this->request->isAjax()){
            $post = $this->request->post();
            $result = $this->validate($post,'app\admin\validate\AssuranceServiceValidate.add');
            if ($result === true){
                AssuranceServiceLogic::add($post);
                $this->_success('添加成功！');
            }
            $this->_error($result);

        }
        return $this->fetch();
    }

    /**
     * @notes 编辑保障服务
     * @param $id
     * @return mixed
     * @author ljj
     * @date 2024/7/12 上午11:15
     */
    public function edit($id)
    {
        if ($this->request->isAjax()){
            $post = $this->request->post();
            $result = $this->validate($post,'app\admin\validate\AssuranceServiceValidate.edit');
            if ($result === true){
                AssuranceServiceLogic::edit($post,$id);
                $this->_success('修改成功');
            }
            $this->_error($result);
        }

        $info = AssuranceServiceLogic::detail($id);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * @notes 删除保障服务
     * @param $id
     * @author ljj
     * @date 2024/7/12 上午11:16
     */
    public function del($id)
    {
        if ($this->request->isAjax()) {
            $result = AssuranceServiceLogic::del($id);
            if ($result) {
                $this->_success('删除成功');
            }
            $this->_error('删除失败');
        }
    }
}