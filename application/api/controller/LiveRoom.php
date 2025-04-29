<?php



namespace app\api\controller;

use app\api\logic\LiveRoomLogic;

/**
 * 直播 控制器层
 * Class LiveRoom
 * @package app\api\controller
 */
class LiveRoom extends ApiBase
{
    public $like_not_need_login = ['lists'];

    /**
     * @notes 获取直播信息列表
     * @author heshihu
     * @date 2021/10/14 10:08
     */
    public function lists()
    {
        $live_room = LiveRoomLogic::lists($this->page_no, $this->page_size);
        // if(is_string($live_room)){
        //     $this->_error($live_room);
        // }
        $this->_success('获取成功', $live_room);
    }


    public function live_update()
    {
        $post =  $this->request->post();
        if(empty($post)){
            $this->_error('同步失败');
        }
        if($post['errMsg'] != 'getChannelsLiveInfo:ok'){
            $this->_error($post['errMsg']);
        }
        $res = LiveRoomLogic::live_update($post,$this->user_id);
        if($res === true){
            $this->_success('成功');
        }
        $this->_error($res);
    }

}