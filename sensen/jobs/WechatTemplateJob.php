<?php

namespace sensen\jobs;

use sensen\basic\BaseJob;
use sensen\services\template\Template;
use think\facade\Route;

/**
 * Class WechatTemplateJob
 * @package sensen\jobs
 */
class WechatTemplateJob extends BaseJob
{
    /**
     * 支付成功发送模板消息
     * @param $order
     * @return bool
     */
    public function sendOrderPaySuccess($openid, $order)
    {
        return $this->sendTemplate('ORDER_PAY_SUCCESS', $openid, [
            'first' => '亲，您购买的商品已支付成功',
            'keyword1' => $order['order_id'],
            'keyword2' => $order['pay_price'],
            'remark' => '点击查看订单详情'
        ], sys_config('site_url') . Route::buildUrl('/pages/order_details/index?order_id=' . $order['order_id'])->suffix('')->domain(false)->build());
    }

    // ...

    /**
     * 发送模板消息
     * @param string $tempCode 模板消息常量名称
     * @param $uid 用户uid
     * @param array $data 模板内容
     * @param string $link 跳转链接
     * @param string|null $color 文字颜色
     * @return bool|mixed
     */
    public function sendTemplate(string $tempCode, $openid, array $data, string $link = null, string $color = null)
    {
        try {
            if (!$openid) return true;
            $template = new Template('wechat');
            $template->to($openid)->color($color);
            if ($link) $template->url($link);
            return $template->send($tempCode, $data);
        } catch (\Exception $e) {
            return true;
        }
    }
}