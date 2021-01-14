<?php

namespace sensen\services\template;

use sensen\basic\BaseManager;
use think\facade\Config;

/**
 * Class Template
 * @package sensen\services\template
 * @mixin \sensen\services\template\storage\Wechat
 * @mixin \sensen\services\template\storage\Subscribe
 */
class Template extends BaseManager
{

    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\sensen\\services\\template\\storage\\';

    /**
     * 设置默认
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('template.default', 'wechat');
    }
}