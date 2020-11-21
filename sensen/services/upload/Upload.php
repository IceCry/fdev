<?php

namespace sensen\services\upload;

use crmeb\basic\BaseManager;
use think\facade\Config;

/**
 * Class Upload
 * @package sensen\services\upload
 * @mixin \sensen\services\upload\storage\Local
 * @mixin \sensen\services\upload\storage\OSS
 * @mixin \sensen\services\upload\storage\COS
 * @mixin \sensen\services\upload\storage\Qiniu
 */
class Upload extends BaseManager
{
    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\crmeb\\services\\upload\\storage\\';

    /**
     * 设置默认上传类型
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('upload.default', 'local');
    }


}