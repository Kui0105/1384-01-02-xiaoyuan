<?php

namespace app\admin\validate;


use think\Validate;

class GoodsOneSpec extends Validate
{
    protected $rule = [
        'one_market_price' => [],
        'one_price'        => 'require|egt:0.01|checkExpress',
        'one_cost_price'   => [],
        'one_stock'        => 'require|integer|gt:0',
        'one_volume'       => [],
        'one_weight'       => [],
    ];

    protected $message = [
        'one_volume.require'        => '请输入体积',
        'one_volume.egt'             => '体积必须为大于或等于0',
        'one_weight.require'        => '请输入重量',
        'one_weight.egt'             => '重量必须为大于或等于0',
//        'one_market_price.require'  => '请输入市场价',
        'one_market_price.egt'      => '市场价不能小于0',
        'one_price.require'         => '请输入价格',
        'one_price.egt'              => '价格必须大于或等于0.01',
        'one_cost_price.require'    => '请输入成本价',
        'one_cost_price.egt'         => '成本价必须大于或等于0.01',
        'one_stock.require'         => '请输入库存',
        'one_stock.integer'         => '库存必须为整型',
        'one_stock.gt'              => '库存必须大于零',
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
                if (empty($data['one_weight']) || $data['one_weight'] <= 0) {
                    return '当前运费模板是按重量计算运费，请输入重量';
                }
                break;
            case 2:
                if (empty($data['one_volume']) || $data['one_volume'] <= 0) {
                    return '当前运费模板是按体积计算运费，请输入体积';
                }
                break;
            default:
                break;
        }
        
        return true;
    }


}