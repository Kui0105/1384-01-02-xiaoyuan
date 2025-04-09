<?php

namespace app\common\behavior;
use app\common\model\Order;
use app\common\server\ConfigServer;
use app\common\server\YlyPrinter;
use think\Db;

class Printer
{
    public function run($params)
    {
        try{
            $order_id = $params['order_id'] ?? '';
            if(empty($order_id)){
                return false;
            }
            //打印机配置
            $printer_config = Db::name('printer_config')
                    ->where(['status'=>1])
                    ->find();

            //打印机列表
            $printer_list = Db::name('printer')
                    ->where(['type'=>$printer_config['id'],'del'=>0,'auto_print'=>1])
                    ->select();

            if(empty($printer_list) || empty($printer_list)){
                return false;
            }
            //获取订单信息
            $order = $this->getOrderList($order_id);
            //获取打印模板
            $template_config = ConfigServer::get('printer', 'yly_template', []);
            //示例化打印机类
            $yly_print = new YlyPrinter($printer_config['client_id'],$printer_config['client_secret']);
            //调用打印机
            $yly_print->ylyPrint($printer_list,$order,$template_config);

            return true;

        }catch (\Exception $e){

            return $e->getMessage();

        }

    }

    /**
     * Notes:获取订单信息
     * @param $id
     * @author: cjhao 2021/4/24 15:22
     */
    public function getOrderList($order_id){
        $order = new Order();

         $order = $order
            ->with(['order_goods'])
            ->where('id', $order_id)
            ->append(['delivery_address'])
            ->find();

        foreach ($order['order_goods'] as &$order_goods) {
            $info = json_decode($order_goods['goods_info'], true);
            $order_goods['name'] = $info['goods_name'];
            $order_goods['spec_value_str'] = $info['spec_value_str'];
            $order_goods['goods_image'] = empty($info['spec_image']) ? $info['image'] : $info['spec_image'];
        }
        return $order;
    }

}