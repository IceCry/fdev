<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 执行用户（Windows下无效）
    'user'     => null,
    // 指令定义
    'commands' => [
        'workerman' => \sensen\command\Workerman::class,
        'timer' => \sensen\command\Timer::class,
    ],
];
