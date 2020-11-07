<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/11/7
 * Time: 15:34
 */

// 事件定义文件
return [
    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'UserLogin'=>[
            \sensen\listeners\user\UserLogin::class
        ]
    ]
];