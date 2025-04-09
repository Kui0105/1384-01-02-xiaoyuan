<?php

namespace app\admin\validate;
use think\Validate;

/**
 * 添加商品 数据校验
 * Class Bargain
 * @package app\admin\validate
 */
class LiveGoods extends Validate{
    protected $rule = [
        'goods_name'            => 'require|min:3|max:14',
        'feedsImg'              => 'require',
        'use_price_type'        => 'checkPrice',
        'url'                   => 'checkUrl',
    ];

    protected $message = [
        'goods_name.require'     => '请输入商品名称',
        'goods_name.min'         => '商品名称必须为3-14个字符',
        'goods_name.max'         => '商品名称必须为3-14个字符',
        'feedsImg.require'       => '请上传商品封面',
    ];


    public function checkPrice($value, $rule, $data)
    {
        switch ($data['use_price_type']) {
            case 1:
                $price = $data['price'];
                break;
            case 2:
                $price = $data['section_price_start'];
                $price2 = $data['section_price_end'];
                if($price > $price2){
                    return '价格不合法';
                }
                break;
            case 3:
                $price = $data['discount_price_start'];
                $price2 = $data['discount_price_end'];
                if($price < $price2){
                    return '价格不合法';
                }
                break;
        }
        if((isset($price) && $price < 0.01) || (isset($price2) && $price2 < 0.01)){
            return '价格不合法';
        }
        return true;
    }

    public function checkUrl($value, $rule, $data)
    {
        $goods_url = explode('=',$value);
        if(!strstr($value, '/') || (isset($goods_url[1]) && !is_numeric($goods_url[1]))){
            return '商品链接不合法';
        }
        return true;
    }
}