<?php

namespace sensen\jobs;

use sensen\basic\BaseJob;
use sensen\services\template\Template;

/**
 * 小程序模板消息消息队列
 * Class RoutineTemplateJob
 * @package sensen\jobs
 */
class RoutineTemplateJob extends BaseJob
{
    /**
     * 确认收货
     * @param $openid
     * @param $order
     * @param $title
     * @return bool
     */
    public function sendOrderTakeOver($openid, $order, $title)
    {
        return $this->sendTemplate('OREDER_TAKEVER', $openid, [
            'thing1' => $order['order_id'],
            'thing2' => $title,
            'date5' => date('Y-m-d H:i:s', time()),
        ], '/pages/order_details/index?order_id=' . $order['order_id']);
    }

    // ...

    /**
     * 发送模板消息
     * @param string $TempCode 模板消息常量名称
     * @param int $openid 用户openid
     * @param array $data 模板内容
     * @param string $link 跳转链接
     * @return bool
     */
    public function sendTemplate(string $tempCode, $openid, array $data, string $link = '')
    {
        try {
            if (!$openid) return true;
            $template = new Template('subscribe');
            return $template->to($openid)->url($link)->send($tempCode, $data);
        } catch (\Exception $e) {
            return true;
        }
    }
}