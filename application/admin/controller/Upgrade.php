<?php



namespace app\admin\controller;


use app\admin\logic\UpgradeLogic;

/** 升级更新
 * Class Upgrade
 * @package app\admin\controller
 */
class Upgrade extends AdminBase
{

    /**
     * Notes: 列表
     * @author 段誉(2021/7/9 11:12)
     * @return mixed
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $page = $this->request->get('page', $this->page_no);
            $size = $this->request->get('limit', $this->page_size);
            $data = UpgradeLogic::index($page, $size);
            $this->_success('', $data);
        }
        return $this->fetch();
    }

    /**
     * Notes: 提示
     * @author 段誉(2021/7/9 11:11)
     * @return mixed
     */
    public function choosePage()
    {
        return $this->fetch();
    }


    /**
     * Notes: 执行更新
     * @author 段誉(2021/7/9 11:11)
     */
    public function handleUpgrade()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['update_type'] = 1;
            $post['link'] = "package_link";
            $result = $this->validate($post, 'app\admin\validate\Upgrade');
            if (true !== $result) {
                $this->_error($result);
            }
            $res = UpgradeLogic::upgrade($post);
            if (true === $res) {
                $this->_success('更新成功');
            }
            $this->_error(UpgradeLogic::getError());
        }
        $this->_error('更新失败');
    }


    // 下载更新包更新
    public function addUpdatePkgLog()
    {
        $post = $this->request->post();
        $res = UpgradeLogic::getPkgLine($post);
        if(false === $res) {
            $this->_error(UpgradeLogic::getError() ?? '系统错误');
        }
        $this->_success('', $res);
    }
}