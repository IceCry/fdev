<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
    ],

    'subscribe' => [
        sensen\subscribes\UserSubscribe::class,//用户事件订阅类
        sensen\subscribes\SystemSubscribe::class,//系统事件订阅类
    ],
];
