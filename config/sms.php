<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/11/7
 * Time: 16:00
 */

return [
    //默认支付模式
    'default' => 'aliyun',
    //单个手机每日发送上限
    'maxPhoneCount' => 10,
    //验证码每分钟发送上线
    'maxMinuteCount' => 20,
    //单个IP每日发送上限
    'maxIpCount' => 50,
    //驱动模式
    'stores' => [
        //阿里云
        'aliyun' => [
            'template_id' => [
                //注册验证码
                'VERIFICATION_CODE'=>'SMS_196220068',

                //通知用户价格被超越
                'BID_NEW_PRICE' => '',
                //订单支付成功通知买家
                'ORDER_PAID' => '',
                //中拍提醒
                'SHOT_NOTICE'=>'',
                //未支付被取消
                'AUCTION_UNPAY_CANCEL'=>'',

                //管理员告警
                'ADMIN_ALERT'=>'',

            ],
            'sign'=>'短信签名',
            'key'=>'xxx',
            'secret'=>'xxx'
        ]
    ]
];