<?php
declare (strict_types = 1);

namespace app;

use sensen\services\ConfigService;
use sensen\services\GroupDataService;
use sensen\utils\Json;
use think\Service;

/**
 * 应用服务类
 */
class AppService extends Service
{
    public $bind = [
        'json'=>Json::class,
        'sysConfig' => ConfigService::class,
        'sysGroupData' => GroupDataService::class
    ];

    public function register()
    {
        // 服务注册
    }

    public function boot()
    {
        // 服务启动
    }
}
