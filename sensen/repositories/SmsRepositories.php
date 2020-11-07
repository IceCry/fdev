<?php
/**
 * Desc: 短信发送
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/1
 * Time: 13:56
 */

namespace sensen\repositories;

use Overtrue\EasySms\EasySms;
use think\facade\Config;

class SmsRepositories
{
    /**
     * 发送短信
     * @param $switch 是否开启
     * @param $phone
     * @param array $data
     * @param string $template
     * @param string $logMsg
     * @return bool|string
     */
    public static function send($switch, $phone, array $data, string $template, $logMsg='')
    {
        if ($switch && $phone) {
            $sign = Config::get('sms.stores.aliyun.sign', '');
            $key = Config::get('sms.stores.aliyun.key', '');
            $secret = Config::get('sms.stores.aliyun.secret', '');
            if(!$sign || !$key || !$secret) return "短信未配置";

            $temp = Config::get('sms.stores.aliyun.template_id.'.$template, '');

            //获取短信模版 缓存
            if(!$temp) return "模版未配置";

            $config = [
                // HTTP 请求的超时时间（秒）
                'timeout' => 5.0,

                // 默认发送配置
                'default' => [
                    // 网关调用策略，默认：顺序调用
                    'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

                    // 默认可用的发送网关
                    'gateways' => [
                        'aliyun'
                    ],
                ],
                // 可用的网关配置
                'gateways' => [
                    'errorlog' => [
                        'file' => './easy-sms.log',
                    ],
                    'aliyun' => [
                        'access_key_id' => $key,
                        'access_key_secret' => $secret,
                        'sign_name' => $sign
                    ]
                ],
            ];
            $easySms = new EasySms($config);
            $res = $easySms->send($phone, [
                'content' => '',
                'template' => $temp,
                'data' => $data,
            ]);
            if($res['aliyun']['status'] == 'success'){
                //记录日志
                return true;
            }else{
                //记录错误
                return false;
            }
        }else{
            return true;
        }
    }
}