<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/12/12
 * Time: 10:33
 */

namespace sensen\services;

use GatewayClient\Gateway;

class GatewayClientService
{
    /**
     * client_id与uid绑定
     * @param $client_id
     * @param $uid
     */
    public static function bindUid($client_id, $uid)
    {
        Gateway::bindUid($client_id, $uid);
    }

    public static function sendToAll($data, $clientArr=[])
    {
        Gateway::sendToAll(json_encode($data), $clientArr);
    }

    public static function sendToUid($uid, $data)
    {
        //todo 待开启
        return true;
        Gateway::sendToUid($uid, json_encode($data));
    }


    public static function joinGroup($client_id, $group)
    {
        Gateway::joinGroup($client_id, $group);
    }

    //todo more

}