<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/23
 */

namespace sensen\services;

use app\models\auction\Deposit;
use app\models\order\Order;
use EasyWeChat\Factory;
use sensen\utils\Hook;
use think\facade\Db;
use think\facade\Route as Url;

/**微信小程序接口
 * Class WechatMinService
 * @package service
 */
class RoutineService
{
    private static $instance = null;

    public static function options()
    {
        $routine = ConfigService::more(['routine_appId', 'routine_appsecret']);

        $config = [
            'app_id' => $routine['routine_appId'] ? trim($routine['routine_appId']) : '',
            'secret' => $routine['routine_appsecret'] ? trim($routine['routine_appsecret']) : '',
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => './routine.log',
            ]
        ];
        return $config;
    }

    public static function application($cache = false)
    {
        (self::$instance === null || $cache === true) && (self::$instance = Factory::miniProgram(self::options()));
        return self::$instance;
    }

    /**
     * 获得用户信息 根据code 获取session_key
     * @param array|string $openid
     * @return $userInfo
     */
    public static function getUserInfo($code)
    {
        $userInfo = self::application()->auth->session($code);
        return $userInfo;
    }

    /**
     * 加密数据解密
     * @param $sessionKey
     * @param $iv
     * @param $encryptData
     * @return $userInfo
     */
    public static function encryptor($sessionKey, $iv, $encryptData)
    {
        return self::application()->encryptor->decryptData($sessionKey, $iv, $encryptData);
    }

    /**
     * 客服消息接口
     * @param null $to
     * @param null $message
     */
    public static function staffService()
    {
        return self::application()->customer_service;
    }

    /**
     * 微信小程序二维码生成接口
     * @return \EasyWeChat\QRCode\QRCode
     */
    public static function qrcodeService($path, $optional)
    {
        return self::application()->app_code->get($path, $optional);
    }

    /**
     * 微信小程序二维码生成接口不限量永久
     * @param $scene
     * @param null $page
     * @param null $width
     * @param null $autoColor
     * @param array $lineColor
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function appCodeUnlimitService($scene, $optional=[])
    {
        return self::application()->app_code->getUnlimit($scene, $optional);
    }

    /**
     * 订阅模板消息接口
     * @return \guoui\services\subscribe\ProgramSubscribe
     */
    public static function SubscribenoticeService()
    {
        return self::application()->now_notice;
    }

    /**
     * 发送订阅消息
     * @param string $touser 接收者（用户）的 openid
     * @param string $templateId 所需下发的订阅模板id
     * @param array $data 模板内容，格式形如 { "key1": { "value": any }, "key2": { "value": any } }
     * @param string $link 击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转。
     */
    public static function sendSubscribeTemlate(string $touser, string $templateId, array $data, string $link = '')
    {
        $data = [
            'template_id' => $templateId, // 所需下发的订阅模板id
            'touser' => $touser,     // 接收者（用户）的 openid
            'page' => $link, // 点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转。
            'data' => $data
        ];

        return self::application()->subscribe_message->send($data);
    }

    /**
     * 支付
     */
    public static function paymentService()
    {
        $routine = ConfigService::more(['wx_mch_id', 'wx_pay_key', 'routine_appId', 'cert_path', 'key_path', 'notify_url']);
        $config = [
            'app_id'             => $routine['routine_appId'],
            'mch_id'             => $routine['wx_mch_id'],
            'key'                => $routine['wx_pay_key'],
            'cert_path'          => $routine['cert_path'],
            'key_path'           => $routine['key_path'],
            'notify_url'         => $routine['notify_url'],
            // 'device_info'     => '013467007045764',
            // 'sub_app_id'      => '',
            // 'sub_merchant_id' => '',
            // ...
        ];

        return Factory::payment($config);
    }

    /**
     * 小程序支付
     * @param string $openid
     * @param string $body
     * @param string $orderSn
     * @param int $totalFee
     * @param string $attach
     * @return array|string
     */
    public static function pay($openid='', $body='支付费用', $orderSn='', $totalFee=0, $attach='')
    {
        $payment = self::paymentService();
        $orderInfo = [
            'body' => $body,
            'out_trade_no' => $orderSn,
            'total_fee' => $totalFee,
            'trade_type' => 'JSAPI',
            'openid'=>$openid,
            'attach'=>$attach
        ];
        $result = $payment->order->unify($orderInfo);
        $jssdk = $payment->jssdk;
        $config = $jssdk->bridgeConfig($result['prepay_id'], false);
        return $config;
    }

    /**
     * 创建直播间
     */
    public static function createLive($data=[])
    {
        //获取上传服务器的图片，传到微信服务器

        /*{
            name: "测试直播房间1",  // 房间名字
            coverImg: "",   // 通过 uploadfile 上传，填写 mediaID
            startTime: 1588237130,   // 开始时间
            endTime: 1588237130 , // 结束时间
            anchorName: "zefzhang1",  // 主播昵称
            anchorWechat: "WxgQiao_04",  // 主播微信号
            shareImg: "" ,  //通过 uploadfile 上传，填写 mediaID
            type: 1 , // 直播类型，1 推流 0 手机直播
            screenType: 0,  // 1：横屏 0：竖屏
            closeLike: 0 , // 是否 关闭点赞 1 关闭
            closeGoods: 0, // 是否 关闭商品货架，1：关闭
            closeComment: 0 // 是否开启评论，1：关闭
        }*/
        $token = (new self())->getAccessToken();
        $createUrl = 'https://api.weixin.qq.com/wxaapi/broadcast/room/create?access_token='.$token;
        //此处curl方法有问题
        $res = HttpService::postRequest($createUrl, json_encode($data), ['Content-Type: application/json']);

        return $res;
    }

    /**
     * 获取access_token
     */
    public function getAccessToken()
    {
        $accessToken = self::application()->access_token;
        $token = $accessToken->getToken();
        $token = $token['access_token'];
        return $token;
    }

    /**
     * 上传临时素材
     * @param $path
     * @return mixed
     */
    public static function upload($path)
    {
        $res = self::application()->media->uploadImage($path);
        return $res['media_id'];
    }

    /**
     * 图片违规检测
     * @param $img
     * @return mixed
     */
    public static function imgSecCheck($img)
    {
        $img = file_get_contents($img);
        $filePath = root_path().'public/uploads/tmp/'.uniqid().'.jpg';
        file_put_contents($filePath, $img);

        $obj = new \CURLFile(realpath($filePath));
        $obj->setMimeType("image/jpeg");
        $file['media'] = $obj;

        $token = (new self())->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/img_sec_check?access_token=$token";

        $info = HttpService::postRequest($url, $file);

        return json_decode($info, true);
    }

    /**
     * 违规内容检测
     * @param $msg
     * @return mixed
     */
    public static function msgSecCheck($msg)
    {
        $token = (new self())->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token=$token";
        $info = HttpService::postRequest($url, json_encode(['content'=>$msg], JSON_UNESCAPED_UNICODE));
        return json_decode($info, true);
    }

    /**
     * OCR识别身份证信息
     * @param $url
     */
    public static function ocr($url, $type='idcard')
    {
        $token = (new self())->getAccessToken();
        if($type=='idcard'){
            $url = "https://api.weixin.qq.com/cv/ocr/idcard?type=MODE&img_url={$url}&access_token={$token}";
        }
        $info = HttpService::postRequest($url);
        return json_decode($info, true);
    }

    /**
     * 微信支付回调
     */
    public static function handleNotify()
    {
        $response = self::paymentService()->handlePaidNotify(function ($message, $fail) {
            // 订单处理
            //file_put_contents('test.txt', json_encode($message));
            //todo 需区分保证金或订单
            if($message['attach']=='deposit'){
                $order = Db::name('deposit_recharge')->where('order_sn', $message['out_trade_no'])->find();
            }else{
                $order = Db::name('orders')->where('order_sn', $message['out_trade_no'])->find();
            }
            if(!$order || $order['pay_time']>0){
                return true;
            }

            if ($message['return_code'] === 'SUCCESS') {
                // 用户是否支付成功
                if ($message['result_code'] === 'SUCCESS') {
                    //保证金支付成功
                    if($message['attach'] == 'deposit'){
                        return Deposit::paySuccess($message['out_trade_no'], $message['transaction_id'], $message['total_fee'], 'routine');
                    }else{
                        //订单支付成功
                        return Order::paySuccess($message['out_trade_no'], $message['transaction_id'], $message['total_fee'], 'routine');
                    }
                }elseif(array_get($message, 'result_code') === 'FAIL') {

                }
            }else{
                return $fail('通信失败，请稍后再通知我');
            }
            // 或者错误消息
            //$fail('Order not exists.');
            return true;
        });
        $response->send();
    }



}