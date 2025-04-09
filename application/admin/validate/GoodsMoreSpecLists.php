<?php

namespace app\admin\validate;


use think\Validate;

class GoodsMoreSpecLists extends Validate
{
    protected $rule = [
        'market_price'  => [],
        'price'         => 'require|egt:0.01|checkExpress',
        'hos_price'     => 'require|egt:0.01|checkExpress',
        'agent_price'   => 'require|egt:0.01|checkExpress',
        'cost_price'    => [],
        'stock'         => 'require|integer|egt:0',
        'weight'        => [],
        'volume'        => [],
    ];

    protected $message = [
        'volume.require'         => '请输入体积',
        'volume.egt'             => '体积必须大于或等于0',
        'weight.require'         => '请输入重量',
        'weight.egt'             => '重量必须大于或等于0',
//        'market_price.require' => '请输入市场价',
        'market_price.egt'       => '市场价不能小于0',
        'price.require'          => '请输入价格',
        'price.egt'              => '价格必须大于或等于0.01',
        'hos_price.require'      => '请输入诊所价格',
        'hos_price.egt'          => '诊所价格必须大于或等于0.01',
        'agent_price.require'    => '请输入代理价格',
        'agent_price.egt'        => '代理价格必须大于或等于0.01',
        'cost_price.require'     => '请输入成本价',
        'cost_price.egt'         => '成本价必须大于或等于0.01',
        'stock.require'          => '请输入库存',
        'stock.integer'          => '库存必须为整数',
        'stock.egt'              => '库存必须大于或等于0',
    ];
    
    function checkExpress($value, $rule, $data)
    {
        $free_shipping_type = input('free_shipping_type');
        
        // 运费模版
        if ($free_shipping_type != 3) {
            return true;
        }
        
        $freight = \app\common\model\Freight::where('id', input('free_shipping_template_id/d'))->findOrEmpty();
        
        if (empty($freight['id'])) {
            return '运费模板不存在';
        }
        
        switch ($freight['charge_way']) {
            case 1:
                if (empty($data['weight']) || $data['weight'] <= 0) {
                    return '当前运费模板是按重量计算运费，规格：' . $data['spec_value_str'] .' 的重量必须大于0';
                }
                break;
            case 2:
                if (empty($data['volume']) || $data['volume'] <= 0) {
                    return '当前运费模板是按体积计算运费，规格：' . $data['spec_value_str'] .' 的体积必须大于0';
                }
                break;
            default:
                break;
        }
        
        return true;
    }


}