<?php

namespace sensen\jobs;

use app\services\message\sms\SmsSendServices;
use sensen\basic\BaseJob;


class SmsAdminJob extends BaseJob
{
    /**
     * 退款发送管理员消息任务
     * @param $switch
     * @param $adminList
     * @param $order
     * @return bool
     */
    public function sendAdminRefund($switch, $adminList, $order)
    {
        if (!$switch) {
            return true;
        }
        /** @var SmsSendServices $smsServices */
        $smsServices = app()->make(SmsSendServices::class);
        foreach ($adminList as $item) {
            $data = ['order_id' => $order['order_id'], 'admin_name' => $item['nickname']];
            $smsServices->send(true, $item['phone'], $data, 'ADMIN_RETURN_GOODS_CODE');
        }
        return true;
    }

    /**
     * 用户确认收货管理员短信提醒
     * @param $switch
     * @param $adminList
     * @param $order
     * @return bool
     */
    public function sendAdminConfirmTakeOver($switch, $adminList, $order)
    {
        if (!$switch) {
            return true;
        }
        /** @var SmsSendServices $smsServices */
        $smsServices = app()->make(SmsSendServices::class);
        foreach ($adminList as $item) {
            $data = ['order_id' => $order['order_id'], 'admin_name' => $item['nickname']];
            $smsServices->send(true, $item['phone'], $data, 'ADMIN_TAKE_DELIVERY_CODE');
        }
        return true;
    }
}