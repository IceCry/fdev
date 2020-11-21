<?php
/**
 * Created by PhpStorm.
 * User: xurongyao <763569752@qq.com>
 * Date: 2019/11/23 3:46 PM
 */

namespace sensen\services\sms;

use crmeb\basic\BaseManager;
use sensen\services\sms\storage\Yunxin;
use think\facade\Config;


/**
 * Class Sms
 * @package sensen\services\sms
 * @mixin Yunxin
 */
class Sms extends BaseManager
{

    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\crmeb\\services\\sms\\storage\\';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('sms.default', 'yunxin');
    }
}