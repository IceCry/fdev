<?php

namespace sensen\services;

use EasyWeChat\Factory;
use think\Response;

class WechatService
{
    private static $instance = null;

    public static function options()
    {
        $wechat = ConfigService::more(['wechat_appid', 'wechat_appsecret', 'wechat_token', 'wechat_encodingaeskey', 'wechat_encode']);
        $payment = ConfigService::more(['pay_weixin_mchid', 'pay_weixin_client_cert', 'pay_weixin_client_key', 'pay_weixin_key']);
        $config = [
            'app_id' => isset($wechat['wechat_appid']) ? trim($wechat['wechat_appid']) : '',
            'secret' => isset($wechat['wechat_appsecret']) ? trim($wechat['wechat_appsecret']) : '',
            'token' => isset($wechat['wechat_token']) ? trim($wechat['wechat_token']) : '',
            'guzzle' => [
                'timeout' => 10.0, // 超时时间（秒）
                'verify' => false
            ],
        ];
        if (isset($wechat['wechat_encode']) && (int)$wechat['wechat_encode'] > 0 && isset($wechat['wechat_encodingaeskey']) && !empty($wechat['wechat_encodingaeskey']))
            $config['aes_key'] = $wechat['wechat_encodingaeskey'];
        $config['payment'] = [
            'app_id' => $config['app_id'],
            'mch_id' => trim($payment['pay_weixin_mchid']),
            'key' => trim($payment['pay_weixin_key']),
            'cert_path' => realpath('.' . $payment['pay_weixin_client_cert']),
            'key_path' => realpath('.' . $payment['pay_weixin_client_key']),
            'notify_url' => sys_config('site_url') . '/api/wechat/notify'
        ];
        return $config;
    }

    public static function application($cache = false)
    {
        (self::$instance === null || $cache === true) && (self::$instance = Factory::officialAccount(self::options()));
        return self::$instance;
    }

    public static function pay()
    {
        $payment = Factory::payment(self::options()['payment']);
        $orderSn = time();
        $result = $payment->order->unify([
            'trade_type' => 'NATIVE',
            'product_id' => 1,
            'body'=>'test',
            'out_trade_no'=>$orderSn,
            'total_fee'=>1
        ]);
        $code = $result['code_url'];
        $path = '/qrcode/pay/'.$orderSn.'.jpg';
        QrCodeService::createQr($code, '.'.$path);
        echo "<img src='".$path."'>";
    }
}
