<?php



namespace app\api\controller;

use app\api\logic\IndexNoticeLogic;


class IndexNotice extends ApiBase
{
    public $like_not_need_login = ['lists','detail'];

    public function lists()
    {
        $type = $this->request->get('type')?:1;
        $article = IndexNoticeLogic::lists($type, $this->page_no, $this->page_size);
        $this->_success('获取成功', $article);
    }


    public function detail()
    {
        $type = $this->request->get('type')?:1;
        $id = $this->request->get('id');
        $notice_detail = IndexNoticeLogic::getDetail($type,$id);
        $this->_success('获取成功', $notice_detail);
    }

}