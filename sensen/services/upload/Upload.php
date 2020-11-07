<?php
/**
 * Desc:
 * User: SenSen Wechat:1050575278
 * Date: 2020/8/6
 * Time: 11:05
 */

namespace sensen\services\upload;

use sensen\basic\BaseManager;
use think\facade\Config;

class Upload extends BaseManager
{
    //空间名
    protected $namespace = '\\sensen\\services\\upload\\storage\\';

    //设置默认上传类型
    protected function getDefaultDriver()
    {
        return Config::get('upload.default', 'local');
    }
}