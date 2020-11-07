<?php

namespace sensen\services;

class ExpressService
{
    /**
     * 暂使用阿里云物流接口
     * @var array
     */
    protected static $api = [
        'query' => 'https://wuliu.market.alicloudapi.com/kdi'
    ];

    /**
     * 查询
     * @param $no 快递号
     * @param string $type 快递公司
     * @return bool
     */
    public static function query($no, $type = '')
    {
        $appCode = sys_config('ali_express_key');
        if (!$appCode) return false;
        $res = HttpService::getRequest(self::$api['query'], compact('no', 'type'), ['Authorization:APPCODE ' . $appCode]);
        $result = json_decode($res, true) ?: false;
        return $result;
    }

}