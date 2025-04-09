<?php

namespace app\api\logic;

use app\common\model\Menu_;
use app\common\model\SelffetchVerifier;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\Db;

class MenuLogic
{
    public static function getMenu($type,$user_info)
    {
        $list = Db::name('menu_decorate')
            ->where(['decorate_type' => $type, 'del' => 0, 'is_show' => 1])
            ->field('name,image,link_type,link_address')
            ->order('sort desc')
            ->select();

        $menu_list = [];

        $is_open = ConfigServer::get('distribution', 'is_open', 1);

        foreach ($list as $key => $menu) {
            //未登录时不显示核销订单入口，登陆用户非核销员时不显示核销订单入口
            if (($menu['link_address'] == Menu_::centre_writeoff_order && empty($user_info)) || ($menu['link_address'] == Menu_::centre_writeoff_order && !empty($user_info) && empty(SelffetchVerifier::where(['user_id'=>$user_info['id'],'status'=>1,'del'=>0])->select()->toArray()))) {
                continue;
            }

            $menu_content = Menu_::getMenuContent($type, $menu['link_address']);

            if ($menu_content && !$is_open && 2 === $menu_content['menu_type']) {
                continue;
            }
            //处理图标
            $menu_list[] = [
                'name' => $menu['name'],
                'image' => UrlServer::getFileUrl($menu['image']),
                'link' => $menu_content['link'] ?? $menu['link_address'],
                'is_tab' => $menu_content['is_tab'] ?? '',
                'link_type' => $menu_content['link_type'] ?? $menu['link_type'],
            ];
        }
        return $menu_list;
    }
}