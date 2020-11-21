<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/9/22
 * Time: 14:59
 */

namespace app\models\wechat;

use app\models\user\User;
use sensen\basic\BaseModel;
use sensen\services\RoutineService;
use sensen\traits\ModelTrait;

class Routine extends BaseModel
{
    use ModelTrait;

    /**
     * 出价被超过
     * @param int $uid
     * @param string $title 拍品价格
     * @param string $price 最新出价
     * @param string $end_time 结束时间
     * @param string $remark 备注信息
     * @param string $link
     * @return bool
     * 拍品名称 {{thing1.DATA}} 最新出价 {{amount2.DATA}} 拍品结束时间 {{time3.DATA}} 备注 {{thing4.DATA}}
     */
    public static function sendBidExceed($uid=0, $title='', $price='', $end_time='', $remark='无', $link='')
    {
        return self::sendOut('NclEsKDTwBzHCEz5c6emTZTOZRbZkVn6Aa5uJjrg5E0', $uid, [
            'thing1' => ['value'=>msubstr($title, 0, 17)],
            'amount2' => ['value'=>$price.'元'],
            'time3' => ['value'=>date('Y/m/d H:i:s', $end_time)],
            'thing4' => ['value'=>msubstr($remark, 0, 17, 'utf-8', false)]
        ], $link);
    }

    /**
     * 中拍通知
     * @param int $uid
     * @param string $orderSn
     * @param string $title
     * @param string $price
     * @param string $endTime
     * @param string $remark
     * @param string $link
     * @return bool
     * 订单号 {{character_string1.DATA}} 商品名称 {{thing2.DATA}} 待支付金额 {{amount3.DATA}} 有效时间 {{time4.DATA}} 备注 {{thing5.DATA}}
     */
    public static function sendShotNotice($uid=0, $orderSn='', $title='', $price='', $endTime='', $remark='无', $link='')
    {
        return self::sendOut('bQfAbd49OltFAoNfgun_09gjhZAOMJ6yCKhwnKYJH2Y', $uid, [
            'character_string1' => ['value'=>$orderSn],
            'thing2' => ['value'=>msubstr($title, 0, 17)],
            'amount3' => ['value'=>$price.'元'],
            'time4' => ['value'=>$endTime],
            'thing5' => ['value'=>msubstr($remark, 0, 17, 'utf-8', false)]
        ], $link);
    }

    /**
     * 拍卖订单未支付被取消通知
     * @param int $uid
     * @param string $orderSn
     * @param string $title
     * @param string $price
     * @param string $time
     * @param string $remark
     * @param string $link
     * @return bool
     */
    public static function sendAuctionOrderCancelNotice($uid=0, $orderSn='', $title='', $price='', $time='', $remark='无', $link='')
    {
        return self::sendOut('_g9-jUQ1euQJ2vQGm0elXyJZcirNf5KE29l9-_CLeJk', $uid, [
            'character_string6' => ['value'=>$orderSn],
            'thing1' => ['value'=>msubstr($title, 0, 17)],
            'amount2' => ['value'=>$price.'元'],
            'date3' => ['value'=>$time],
            'thing5' => ['value'=>msubstr($remark, 0, 17, 'utf-8', false)]
        ], $link);
    }

    /**
     * 支付成功通知
     * @param int $uid
     * @param string $title
     * @param string $orderSn
     * @param string $price
     * @param string $time
     * @param string $remark
     * @param string $link
     * @return bool
     * 订单编号 {{character_string9.DATA}} 商品名称 {{thing12.DATA}} 支付金额 {{amount4.DATA}} 支付时间 {{time6.DATA}} 备注 {{thing7.DATA}}
     */
    public static function sendPaidNotice($uid=0, $title='', $orderSn='', $price='', $time='', $remark='无', $link='')
    {
        return self::sendOut('4iWUZmfRXKH6TKEFVLh02RKAy0Q_53IwGRz2aceqoW4', $uid, [
            'character_string8' => ['value'=>$orderSn],
            'thing12' => ['value'=>msubstr($title, 0, 17)],
            'amount4' => ['value'=>$price.'元'],
            'time6' => ['value'=>date('Y/m/d H:i:s', $time)],
            'thing7' => ['value'=>msubstr($remark, 0, 20, 'utf-8', false)],
        ], $link);
    }

    /**
     * 认证结果通知用户
     * @param int $uid
     * @param string $title 类型 标题
     * @param string $result 通过  拒绝
     * @param string $reason 审核理由
     * @param string $link
     * @return bool
     * 类型 {{phrase5.DATA}} 审核结果 {{thing1.DATA}} 备注 {{thing3.DATA}}
     */
    public static function sendVerifyResult($uid=0, $title='', $result='', $reason='无', $link='')
    {
        return self::sendOut('oiHpv7NFhRIO6xCqFofa1w7ELv20gtkvhLxRruZzCok', $uid, [
            'phrase5' => ['value'=>$title],
            'thing1' => ['value'=>$result],
            'thing3' => ['value'=>msubstr($reason, 0, 17)]
        ], $link);
    }

    /**
     * 入会结果通知
     */
    public static function sendVipResult()
    {

    }

    /**
     * 订单发货提醒
     * @param int $uid
     * @param string $title
     * @param string $orderSn
     * @param string $expressName
     * @param string $expressId
     * @param string $remark
     * @param string $link
     * @return bool
     * 商品信息 {{thing2.DATA}} 订单号 {{character_string8.DATA}} 快递公司 {{thing9.DATA}} 快递单号 {{character_string10.DATA}} 备注 {{thing12.DATA}}
     */
    public static function sendExpressNotice($uid=0, $title='', $orderSn='', $expressName='', $expressId='', $remark='无', $link='')
    {
        return self::sendOut('4iWUZmfRXKH6TKEFVLh02RKAy0Q_53IwGRz2aceqoW4', $uid, [
            'thing2' => ['value'=>msubstr($title, 0, 17)],
            'character_string8' => ['value'=>$orderSn],
            'thing9' => ['value'=>msubstr($expressName, 0, 17)],
            'character_string10' => ['value'=>$expressId],
            'thing12' => ['value'=>msubstr($remark, 0, 20, 'utf-8', false)],
        ], $link);
    }

    /**
     * 提现成功通知
     * @param int $uid
     * @param string $price
     * @param string $remark
     * @param string $link
     * @return bool
     * 提现金额 {{amount3.DATA}} 备注信息 {{thing5.DATA}}
     */
    public static function sendWithdrawSuccess($uid=0, $price='', $remark='无', $link='')
    {
        return self::sendOut('F7DMD6PKOKYx8dW5vjmDX7NPdMUgT9Uz_23yyQlkNr0', $uid, [
            'amount3' => ['value'=>$price.'元'],
            'thing5' => ['value'=>msubstr($remark, 0, 20, 'utf-8', false)],
        ], $link);
    }

    /**
     * 提现失败通知
     * @param int $uid
     * @param string $price
     * @param string $remark
     * @param string $link
     * @return bool
     * 提现金额 {{amount3.DATA}} 失败原因 {{thing4.DATA}}
     */
    public static function sendWithdrawFail($uid=0, $price='', $remark='无', $link='')
    {
        return self::sendOut('xDIbubg3OgIF0SYAc8Cc3Srg1B5ucfqbLIAQ8HPetgc', $uid, [
            'amount3' => ['value'=>$price.'元'],
            'thing4' => ['value'=>msubstr($remark, 0, 20, 'utf-8', false)],
        ], $link);
    }

    /**
     * 充值成功通知
     * @param int $uid
     * @param string $order_sn
     * @param string $price
     * @param string $time
     * @param string $remark
     * @param string $link
     * @return bool
     * 充值单号 {{number2.DATA}} 充值金额 {{amount3.DATA}} 充值时间 {{time4.DATA}} 温馨提示 {{thing5.DATA}}
     */
    public static function sendRechargeSuccess($uid=0, $order_sn='', $price='', $time='', $remark='无', $link='')
    {
        return self::sendOut('hrrDDh1krh8PBz190aeyF_q_5XIG2j2qEJX7z2Ch1iY', $uid, [
            'number2' => ['value'=>$order_sn],
            'amount3' => ['value'=>$price.'元'],
            'time4' => ['value'=>$time],
            'thing5' => ['value'=>msubstr($remark, 0, 20, 'utf-8', false)],
        ], $link);
    }

    /**
     * 发送消息
     * @param $tplId
     * @param $uid
     * @param $data
     * @param $link
     * @return bool
     */
    public static function sendOut($tplId, $uid, $data, $link)
    {
        try {
            $openid = User::getOpenIdByUid($uid);
            if (!$openid) return false;
            $res = RoutineService::sendSubscribeTemlate($openid, $tplId, $data, $link);
            //file_put_contents('test.txt', json_encode($res));
        } catch (\Exception $e) {
            return false;
        }
    }

}