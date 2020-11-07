<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/31
 * Time: 15:45
 */

namespace app\http;

use app\models\collection\Collection;
use think\worker\Server;
use Workerman\Lib\Timer;

class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2345';

    /*public function onWorkerStart($worker)
    {
        //暂定5秒广播一次
        //无法获取uuid无法使用广播竞拍数据
        Timer::add(5, function () use ($worker)
        {
            //遍历并推数据
            foreach($worker->connections as $connection)
            {
                $connection->send(time());
            }
        });
    }*/

    public function onMessage($connection, $data)
    {
        $receiver = json_decode($data, true);
        if($receiver['type'] == 'ping'){
            $connection->send(json_encode(['data'=>'pong']));
        }else if($receiver['type'] == 'bid'){
            $info = [];
            $connection->send(json_encode($info));
        }else{
            $connection->send(json_encode($data));
        }
    }
}