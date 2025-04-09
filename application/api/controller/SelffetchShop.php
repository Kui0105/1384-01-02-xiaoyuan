<?php


namespace app\api\controller;


use app\api\logic\SelffetchShopLogic;

class SelffetchShop extends ApiBase
{
    /**
     * @notes 查看自提门店列表
     * @author ljj
     * @date 2021/8/24 4:59 下午
     */
    public function lists()
    {
        $params['longitude'] = $this->request->get('longitude', '113.280637');
        $params['latitude'] = $this->request->get('latitude', '23.125178');
        $lists = SelffetchShopLogic::lists($params, $this->page_no, $this->page_size);
        $this->_success('获取成功', $lists);
    }

    /**
     * @notes 获取百度地图密钥
     * @author ljj
     * @date 2021/8/24 5:01 下午
     */
    public function getMapKey()
    {
        $result = SelffetchShopLogic::getMapKey();
        $this->_success('获取成功', $result);
    }
}