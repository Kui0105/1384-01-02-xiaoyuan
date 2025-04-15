<?php



namespace app\api\controller;

use app\api\logic\ArticleLogic;


class Article extends ApiBase
{

    public $like_not_need_login = ['lists', 'category', 'detail'];

    public function lists()
    {
        $type = $this->request->get('type')?:1;
        $get = $this->request->get();
        $article = ArticleLogic::lists($type,$get,$this->page_no, $this->page_size);
        $this->_success('获取成功', $article);
    }


    public function category()
    {
        $type = $this->request->get('type')?:1;
        $article = ArticleLogic::getTwoCategory($type);
        $this->_success('获取成功', $article);
    }


    public function detail()
    {
        $id = $this->request->get('id');
        if (!$id) {
            $this->_error('参数缺失');
        }
        $article_detail = ArticleLogic::getArticleDetail($id,$this->user_id);
        $this->_success('获取成功', $article_detail);
    }

    public function a_collect()
    {
        $id = $this->request->get('id');
        if (!$id) {
            $this->_error('缺少参数');
        }
        $like = ArticleLogic::ACollect($this->user_id, $id);
        if ($like) {
            $this->_success($like);
        }
        $this->_error(ArticleLogic::getError() ?? '失败');
    }
}